package org.argus.sms.app.smsConfig.utility;

import android.content.Context;
import android.util.Log;

import org.argus.sms.app.smsConfig.IncomingSMS;
import org.argus.sms.app.smsConfig.security.HMAC;
import org.argus.sms.app.smsConfig.security.NovelTEncryption;
import org.argus.sms.app.utils.HelperPreference;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Created by eotin on 27/06/2017.
 */

public class ConfigSMSParser {
    private final static String TAG = ConfigSMSParser.class.getSimpleName();

    public static final String CONFIG_SMS_KEYWORD = "ARGCFG";
    public static final String CONFIG_ACK_SMS_KEYWORD = "ARGACK";
    public static final String CONFIG_SMS_SEPARATOR = "#";

    private String keyWord;
    private String imei;
    private int id;
    private String cmd;
    private String setting;
    private String value;
    private String hash;

    private String securityKey;
    private String encodedMessage;
    private String message;

    private static final Pattern CONFIG_SMS_PATTERN_ENCODED
            = Pattern.compile(String.format("^(%1$s){1}%2$s(.*)$",
            CONFIG_SMS_KEYWORD,
            CONFIG_SMS_SEPARATOR)); // https://regex101.com/r/zuvhOs/1

    /* Regex parsing */
    private static final Pattern CONFIG_SMS_PATTERN
            = Pattern.compile(String.format("^(%1$s){1}%2$s(\\d+)%2$s(\\d+)%2$s([A-Z]{3,}){1}%2$s([^%2$s]+){1}(%2$s([^%2$s]+))?%2$s(.+)%2$s$",
            CONFIG_SMS_KEYWORD,
            CONFIG_SMS_SEPARATOR)); // https://regex101.com/r/nskV0b/9

    /**
     * Check if SMS match CONFIG_SMS_PATTERN_ENCODED
     *
     * @param sms
     * @return
     */
    public static boolean isConfigSMS(IncomingSMS sms)
    {
        if (sms== null || sms.getMessage() == null || sms.getMessage().isEmpty()) {
            return false ;
        }

        Matcher matcher = CONFIG_SMS_PATTERN_ENCODED.matcher(sms.getMessage());
        return matcher.find();
    }

    public static ConfigSMSParser parseSMS(IncomingSMS sms, String securityKey, boolean isEncrypted)
    {
        ConfigSMSParser instance = new ConfigSMSParser();
        instance.securityKey = securityKey;

        if (isEncrypted) {
            // Les SMS sont cryptés
            instance.setEncodedMessage(sms.getMessage()) ;
            if (! instance.decryptMessage()) {
                return null;
            }
        } else {
            instance.setEncodedMessage(sms.getMessage()) ;
            instance.setMessage(sms.getMessage()) ;
        }

        Matcher matcher = CONFIG_SMS_PATTERN.matcher(instance.getMessage());

        if (matcher.find()) {
            instance.setKeyWord(matcher.group(1));
            instance.setImei(matcher.group(2));
            instance.setId(Integer.valueOf(matcher.group(3)));
            instance.setCmd(matcher.group(4));
            instance.setSetting(matcher.group(5));
            // Group 6 equals group 7 with CONFIG_SMS_SEPARATOR at the beginning
            if (matcher.groupCount() >= 7) {
                instance.setValue(matcher.group(7));
            }

            // Hash code
            if (matcher.groupCount() >= 8) {
                instance.setHash(matcher.group(8));
            }
        } else {
            return null ;
        }

        return instance ;
    }

    /**
     * Decrypt message
     *
     * @return
     */
    private boolean decryptMessage()
    {
        Matcher encodedMatcher = CONFIG_SMS_PATTERN_ENCODED.matcher(this.getEncodedMessage());
        if (! encodedMatcher.find()) {
            return false ;
        }

        this.setKeyWord(encodedMatcher.group(1));
        String encodedPayload = encodedMatcher.group(2);
        String decodedPayload = NovelTEncryption.decrypt(encodedPayload);

        this.message = this.getKeyWord() + CONFIG_SMS_SEPARATOR + decodedPayload;

        return true ;
    }

    public String getKeyWord() {
        return keyWord;
    }

    public void setKeyWord(String keyWord) {
        this.keyWord = keyWord;
    }

    public String getImei()
    {
        return this.imei;
    }

    public void setImei(String imei)
    {
        this.imei = imei;
    }

    public int getId()
    {
        return id;
    }

    public void setId(int id)
    {
        this.id = id;
    }

    public String getCmd() {
        return cmd;
    }

    public void setCmd(String cmd) {
        this.cmd = cmd;
    }

    public String getSetting() {
        return setting;
    }

    public void setSetting(String setting) {
        this.setting = setting;
    }

    public String getValue() {
        return value;
    }

    public void setValue(String value)
    {
        this.value = value;
    }

    public String getHash()
    {
        return hash;
    }

    public void setHash(String hash)
    {
        this.hash = hash;
    }

    public String getEncodedMessage()
    {
        return encodedMessage;
    }

    public void setEncodedMessage(String encodedMessage)
    {
        this.encodedMessage = encodedMessage;
    }

    public String getMessage()
    {
        return this.message;
    }

    public void setMessage(String message)
    {
        this.message = message;
    }

    private String getSecurityKey()
    {
        return this.securityKey ;
    }

    public boolean isConfigSmsValid(Context context)
    {
        return this.isHashValid() && this.isImeiValid(context);
    }

    public boolean isImeiValid(Context context)
    {
       String imei = HelperPreference.getUniqueDeviceNumber(context);

        if (this.getImei() == null
                || this.getImei().isEmpty()
                || !this.getImei().trim().toUpperCase().equals(imei.trim().toUpperCase())) {
            Log.e(TAG, "IMEI is not recognized");
            return false;
        }

        return true;
    }

    /**
     * Calculate hash and compare it to the received hash
     *
     * @return
     */
    public boolean isHashValid()
    {
        if (this.hash == null || this.hash.isEmpty()) {
            return false ;
        }

        StringBuilder builder = new StringBuilder();
        if (this.getImei() != null) {
            builder.append(this.getImei());
        }

        builder.append(this.getId());

        if (this.getCmd() != null) {
            builder.append(this.getCmd());
        }

        if (this.getSetting() != null) {
            builder.append(this.getSetting());
        }

        if (this.getValue() != null) {
            builder.append(this.getValue());
        }

        String data = builder.toString();
        String hash = HMAC.hmacDigest(data, this.getSecurityKey(), HMAC.HMACMD5);

        return hash.contains(this.hash) ;
    }
}
