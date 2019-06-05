package org.argus.sms.app.smsConfig;

import org.argus.sms.app.smsConfig.security.NovelTEncryption;
import org.argus.sms.app.smsConfig.utility.ConfigSMSParser;

import java.security.InvalidParameterException;
import java.util.Locale;

/**
 * Created by eotin on 04/07/2017.
 */

public class OutgoingSMS
{
    public enum ANSWER_CONFIG_ERROR_CODE
    {
        OK ("OK", 0), // OK
        // ERRORS
        UNKNOWN_CONFIG_FORMAT ("KO", 1),
        WRONG_HASH ("KO", 2),
        WRONG_IMEI ("KO", 3),
        UNKNOWN_COMMAND ("KO", 4),
        UNKNOWN_SETTING ("KO", 5),
        EMPTY_VALUE ("KO", 6),
        // Some settings cannot be editable
        NON_EDITABLE_SETTING ("KO", 7),
        // Other Error
        OTHER ("KO", 10),

        // EXCEPTION
        EXCEPTION ("KO", 20);

        private int _value;
        private String _message;

        ANSWER_CONFIG_ERROR_CODE(String message, int value) {
            this._message = message;
            this._value = value;
        }

        public int getValue() {
            return this._value;
        }

        public String getMessage() {
            return this._message;
        }

        public static ANSWER_CONFIG_ERROR_CODE fromInt(int i) {
            for (ANSWER_CONFIG_ERROR_CODE b : ANSWER_CONFIG_ERROR_CODE.values()) {
                if (b.getValue() == i) { return b; }
            }
            return null;
        }
    }

    public static int LENGTH_SMS_MAX = 160;

    protected String to;
    protected int smsId;
    protected String imei;

    private String message;
    private  String encryptMessage;

    public OutgoingSMS()
    {

    }

    public OutgoingSMS(String to,int smsId, String imei) throws InvalidParameterException
    {
        this.to = to;
        this.smsId = smsId;
        this.imei = imei;
    }

    public String getMessage()
    {
        return this.message;
    }

    public String getEncryptMessage()
    {
        return this.encryptMessage;
    }

    protected void setMessages(String... args)
    {
        String prefix = String.format(Locale.US, "%1$s%2$s",
                ConfigSMSParser.CONFIG_ACK_SMS_KEYWORD,
                ConfigSMSParser.CONFIG_SMS_SEPARATOR);

        String message =  String.format(Locale.US, "%1$s%2$s%3$s",
                this.imei,
                ConfigSMSParser.CONFIG_SMS_SEPARATOR,
                this.smsId);

        for (String arg : args) {
            message += String.format(Locale.US, "%1$s%2$s",
                    ConfigSMSParser.CONFIG_SMS_SEPARATOR,
                    arg);
        }


        this.message = prefix + message;
        this.encryptMessage = prefix + NovelTEncryption.encrypt(message);
    }

    public String getTo()
    {
        return to;
    }

    public int getSmsId()
    {
        return this.smsId;
    }
}
