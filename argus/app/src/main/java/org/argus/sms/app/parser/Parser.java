package org.argus.sms.app.parser;

import android.text.TextUtils;
import android.util.Log;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Set;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.ConfigApp;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.model.TypeData;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.utils.HelperConstraint;
import org.argus.sms.app.utils.HelperSms;

/**
 * Class to analyse and Create a Sms object from an SMS text.
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class Parser {
    private final static String TAG = Parser.class.getSimpleName();
    private final static boolean DEBUG = true;
    public final static String SEPARATOR_SPACE = " ";
    public final static String SEPARATOR_FIELDS = ",";
    public final static String SEPARATOR_COMMA = ",";
    public final static String SEPARATOR_EQUALS = "=";

    public final static String TEMPLATE_WEEKLY = "TW:";
    public final static String TEMPLATE_MONTHLY = "TM:";
    public final static String TEMPLATE_ALERT = "TA:";
    public final static String TEMPLATE_CONF = "CF:";
    public final static String TEMPLATE_HEALTHFACILITY = "HFName";

    public final static String TEMPLATE_CONF_ALERT = "M4ConfAlert";
    public final static String TEMPLATE_CONF_WEEKLY = "M4ConfW";
    public final static String TEMPLATE_CONF_MONTHLY = "M4ConfM";


    /**
     * Check if a sms is a config sms
     * @param smsText sms to check
     * @return true if the given message is a config sms, false otherwise
     */
    public static boolean isConfig(String smsText) {
        boolean isConfig = false;
        if (TextUtils.isEmpty(smsText)) {
            return false;
        } else {
            if (smsText.contains(TEMPLATE_WEEKLY)) {
                isConfig = true;
            } else if (smsText.contains(TEMPLATE_MONTHLY)) {
                isConfig = true;
            } else if (smsText.contains(TEMPLATE_ALERT)) {
                isConfig = true;
            } else if (smsText.contains(TEMPLATE_CONF)) {
                isConfig = true;
            }
        }
        return isConfig;
    }

    /**
     * Get the config type of the config message
     * Only call if isConfig is true
     * @param smsText sms to check
     * @return type of config sms
     */
    public static TypeSms getConfigType(String smsText) {
        TypeSms type = TypeSms.OTHER;
        if (!TextUtils.isEmpty(smsText)) {
            if (smsText.contains(TEMPLATE_WEEKLY)) {
                type = TypeSms.MODEL;
            } else if (smsText.contains(TEMPLATE_MONTHLY)) {
                type = TypeSms.MODEL;
            } else if (smsText.contains(TEMPLATE_HEALTHFACILITY)) { // Warning : this sms also contain TEMPLATE_CONF
                type = TypeSms.CONFIG;
            } else if (smsText.contains(TEMPLATE_CONF)) {
                type = TypeSms.CONFIG;
            }  else if (smsText.contains(TEMPLATE_ALERT)) {
                type = TypeSms.MODEL;
            }
        }
        return type;
    }

    /**
     * Get the subtype of an sms
     * @param smsText sms to check
     * @return the sms subtype
     */
    public static SubTypeSms getSubType(String smsText) {
        SubTypeSms type = SubTypeSms.MODEL_NONE;
        if (!TextUtils.isEmpty(smsText)) {
            if (smsText.contains(TEMPLATE_WEEKLY)) {
                type = SubTypeSms.MODEL_WEEKLY;
            } else if (smsText.contains(TEMPLATE_MONTHLY)) {
                type = SubTypeSms.MODEL_MONTHLY;
            } else if (smsText.contains(TEMPLATE_HEALTHFACILITY)) { // Warning : this sms also contain TAMPLATE_CONF
                type = SubTypeSms.MODEL_HEALTHFACILITY;
            } else if (smsText.contains(TEMPLATE_CONF)) {
                type = getSubTypeForConfig(smsText);
            } else if (smsText.contains(TEMPLATE_ALERT)) {
                type = SubTypeSms.MODEL_ALERT;
            }
        }
        return type;
    }


    /**
     * Get the subtype of an config sms
     * Only call if isConfig is true
     * @param smsText sms to check
     * @return the sms subtype
     */
    private static SubTypeSms getSubTypeForConfig(final String smsText) {
        if (smsText.contains(TEMPLATE_CONF_ALERT)) {
            return SubTypeSms.MODEL_ALERT;
        } else if (smsText.contains(TEMPLATE_CONF_MONTHLY)) {
            return SubTypeSms.MODEL_MONTHLY;
        } else if (smsText.contains(TEMPLATE_CONF_WEEKLY)) {
            return SubTypeSms.MODEL_WEEKLY;
        } else {
            return SubTypeSms.MODEL_NONE;
        }
    }

    /**
     * Get the type of sms from the recieved sms and the config
     * @param smsText sms text received
     * @param config configuration of the app
     * @return the type of sms
     */
    public static TypeSms getTypeFromText(String smsText, Config config) {
        if (TextUtils.isEmpty(smsText)) {
            return null;
        } else {
            TypeSms type;
            if ((type = getConfigType(smsText)) != TypeSms.OTHER) {
                return type;
            }
            if (smsText.contains(ConfigApp.SMS_OK)) {
                if (smsText.contains(ConfigApp.CODE_OK_REPORT) || smsText.contains(ConfigApp.CODE_OK_ALERT)) {
                    return TypeSms.CONFIRM;
                } else if (smsText.contains(ConfigApp.CODE_ERROR)) {
                    return TypeSms.ERROR;
                } else {
                    return TypeSms.OTHER;
                }
            }else if (HelperSms.isMessageThreshold(smsText)){
                return TypeSms.THRESHOLD;
            }
            String[] parts = smsText.split(" ");
            if (parts.length > 0) {
                String firstPart = parts[0];
                if (firstPart.contains(":")) {
                    String key = firstPart.replace(":", "");
                    if (config.getValueForKey(key) != null) {
                        return TypeSms.MODEL;
                    } else {
                        return TypeSms.ERROR;
                    }
                } else {
                    String key = config.getKeyForValue(firstPart);
                    if (key != null) {
                        if (key.equals(Config.KEYWORD_ALERT)) {
                            return TypeSms.ALERT;
                        } else {
                            return TypeSms.CONFIRM;
                        }
                    }
                }
            }
            return TypeSms.OTHER;
        }
    }

    /**
     * Parse an Sms from the textmessage and the config
     * @param smsText sms text received
     * @param config configuration of the app
     * @return
     */
    public static Sms getSmsFromText(String smsText, Config config) {
        if (TextUtils.isEmpty(smsText)) {
            return null;
        } else {
            Sms sms = new Sms();
            sms.mType = getTypeFromText(smsText, config);
            if (sms.mType != null && sms.mType != TypeSms.OTHER) {
                sms.mDisease = getFieldFromSms(Config.KEYWORD_DISEASE, smsText, config);
                sms.mWeek = getFieldFromSms(Config.KEYWORD_WEEK, smsText, config);
                sms.mMonth = getFieldFromSms(Config.KEYWORD_MONTH, smsText, config);
                sms.mYear = getFieldFromSms(Config.KEYWORD_YEAR, smsText, config);
                sms.mLabel = getFieldFromSms(Config.KEYWORD_LABEL, smsText, config);
                sms.mListData = getOtherFieldsFromSms(smsText, config);
            }
            return sms;
        }
    }

    /**
     * Get a specific field from the sms
     * @param field field to extract
     * @param smsText sms text received
     * @param config configuration of the app
     * @return the field found in message or null if not found
     */
    public static String getFieldFromSms(String field, String smsText, Config config) {
        String result = null;
        if (config == null) {
            throw new IllegalArgumentException("config must not be null");
        }
        if (!TextUtils.isEmpty(smsText)) {
            String valueForKey = config.getValueForKey(field);
            if (!TextUtils.isEmpty(valueForKey) && smsText.contains(valueForKey)) {
                String[] split = smsText.split(SEPARATOR_SPACE);
                for (String bigParts : split) {
                    if (bigParts.contains(valueForKey)) {
                        String[] commaSeparated = bigParts.split(SEPARATOR_COMMA);
                        for (String s : commaSeparated) {
                            if (s.contains(valueForKey)) {
                                String[] data = s.split(SEPARATOR_EQUALS);
                                if (data.length == 2) {
                                    result = data[1];
                                    return result;
                                } else {
                                    if (BuildConfig.DEBUG && DEBUG) {
                                        Log.d(TAG, "getFieldFromSms data split not equals to 2");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return result;
    }

    /**
     * Get  a specific field with spaces from the sms
     * @param field field to extract
     * @param smsText sms text received
     * @param config configuration of the app
     * @return the field found in message or null if not found
     */
    public static String getSpacedFieldFromSms(String field, String smsText, Config config) {
        String result = null;
        if (config == null) {
            throw new IllegalArgumentException("config must not be null");
        }
        if (!TextUtils.isEmpty(smsText)) {
            String valueForKey = config.getValueForKey(field);
            if (!TextUtils.isEmpty(valueForKey) && smsText.contains(valueForKey)) {
                String[] commaSeparated = smsText.split(SEPARATOR_COMMA);
                for (String s : commaSeparated) {
                    if (s.contains(valueForKey)) {
                        String[] data = s.split(SEPARATOR_EQUALS);
                        if (data.length == 2) {
                            result = data[1];
                            return result;
                        } else {
                            if (BuildConfig.DEBUG && DEBUG) {
                                Log.d(TAG, "getFieldFromSms data split not equals to 2");
                            }
                        }
                    }
                }
            }
        }
        return result;
    }

    /***
     * Class used to manage Constraint between fields in UI configuration
     */
    public static class Constraint{
        public String   FieldFrom ;
        public String   FieldTo ;
        public HelperConstraint.Constraint  Constraint = null;

        public Constraint(String fieldFrom, String fieldTo, HelperConstraint.Constraint constraint) {
            this.FieldFrom = fieldFrom;
            this.FieldTo = fieldTo;
            this.Constraint = constraint;
        }
    }

    /**
     * Class used to parse UI configuration sms as Alert conf or weekly report ....
     */
    public static class ConfigField {

        public String                       Type;
        public String                       Name;
        public boolean                      IsOptionnal = false;

        public ConfigField(String name, String type) {
            this.Type = type;
            this.Name = name;
        }
        public ConfigField(String name, String type, boolean isOptionnal) {
            this.Type = type;
            this.Name = name;
            this.IsOptionnal = isOptionnal;
        }

        public int GetFieldLength() {
            int fieldLength = (Name + Parser.SEPARATOR_EQUALS).length();
            if (Type.equals(TypeData.NUMBER.toString()) || Type.isEmpty()) {
                fieldLength += 4;
            } else if (Type.equals(TypeData.DATE.toString())) {
                fieldLength += 10;
            } else {
                fieldLength += 15;
            }

            return fieldLength;
        }
    }


    public static  List<Constraint> getConstraintsFromSmsReport(String smsText, Config config){
        List<Constraint> result = null;
        if (config == null) {
            throw new IllegalArgumentException("config must not be null");
        }

        if (!TextUtils.isEmpty(smsText)) {
            smsText = HelperSms.removeAndroidIdFromString(smsText);
            smsText = HelperSms.removeFirstKeywordInString(smsText);
            smsText = HelperSms.removeFirstWordInString(smsText);
            result = new ArrayList<Constraint>();
            String[] commaSeparated = smsText.split(SEPARATOR_FIELDS);
            for (String s : commaSeparated) {
                addConstraint(config,  result, s);
            }
        }
        return result;
    }

    /**
     * Add a constraintin the result list
     * @param config
     * @param result list where the constraints must be added
     * @param s string need to be transformed into Constraint
     */
    private static void addConstraint(Config config, List<Constraint> result, String s) {
        if (HelperConstraint.isConstraint(s)) {
            HelperConstraint.Constraint c = HelperConstraint.getConstraint(s);
            if (c != null) {
                String[] data = s.split(HelperConstraint.ConstraintLibrary[c.getValue()]);
                if (data.length == 2) {
                    Constraint co = new Constraint(data[0], data[1],c);
                    result.add(co);
                }
            }
        }
    }

    /**
     * Get all fields not referenced in the Config, used to configure report view
     * @param smsText sms text received
     * @param config configuration of the app
     * @return result : a list of configuration field
     */
    public static List<ConfigField> getOtherFieldsFromSmsReport(String smsText, Config config) {
        List<ConfigField> result = null;
        if (config == null) {
            throw new IllegalArgumentException("config must not be null");
        }
        if (!TextUtils.isEmpty(smsText)) {
            smsText = HelperSms.removeAndroidIdFromString(smsText);
            smsText = HelperSms.removeFirstKeywordInString(smsText);
            smsText = HelperSms.removeFirstWordInString(smsText);
            result = new ArrayList<ConfigField>();
            String[] commaSeparated = smsText.split(SEPARATOR_FIELDS);
            for (String s : commaSeparated) {
                addField(config,  result, s);
            }
        }
        return result;
    }

    /**
     * Add a config field in the result list
     * @param config
     * @param result list where the config field must be added
     * @param s string need to be transformed into ConfigField
     */
    private static void addField(Config config, List<ConfigField> result, String s) {
        if (!HelperConstraint.isConstraint(s)) {
            String[] data = s.split(SEPARATOR_EQUALS);
            if (data.length == 2) {
                if (config.getKeyForValue(data[0]) == null) {
                    ConfigField cf = new ConfigField(data[0], data[1]);
                    result.add(cf);
                }
            } else {
                if (config.getKeyForValue(s) == null) {
                    ConfigField cf = new ConfigField(s, "");
                    result.add(cf);
                }
            }
        }
    }

    /**
     * Get all fields not referenced in the Config, used to configure view like Alert view
     * @param smsText sms text received
     * @param config configuration of the app
     * @return
     */
    public static List<ConfigField> getOtherFieldsFromSms(String smsText, Config config) {
        List<ConfigField> result = null;
        if (config == null) {
            throw new IllegalArgumentException("config must not be null");
        }
        if (!TextUtils.isEmpty(smsText)) {
            smsText = HelperSms.removeAndroidIdFromString(smsText);
            smsText = HelperSms.removeFirstKeywordInString(smsText);
            smsText = HelperSms.removeFirstWordInString(smsText);
            result = new ArrayList<ConfigField>();
            String[] commaSeparated = smsText.split(SEPARATOR_FIELDS);
            for (String s : commaSeparated) {
                if (!HelperConstraint.isConstraint(s)) {
                    String[] data = s.split(SEPARATOR_EQUALS);
                    if (data.length == 2) {
                        // do not add known keyword fields
                        if (config.getKeyForValue(data[0]) == null) {
                            boolean isOpt = false;
                            if (data[1].contains(" 0")) {
                                isOpt = true;
                                data[1] = data[1].replace(" 0", "");
                            }
                            ConfigField cf = new ConfigField(data[0], data[1], isOpt);
                            result.add(cf);
                        }
                    } else {
                        if (BuildConfig.DEBUG && DEBUG) {
                            Log.d(TAG, "getFieldFromSms data split not equals to 2");
                        }
                    }
                }
            }
        }
        return result;
    }

    /**
     * Parse all fields for a specific separator
     * @param text text to split
     * @param separator separator
     * @return a map of fields
     */
    public static HashMap<String, String> parseFieldsForSeparator(String text, String separator) {
        HashMap<String, String> map = new HashMap<String, String>();
        String[] parts = text.split(separator);
        for (String part : parts) {
            String[] item = part.split(SEPARATOR_EQUALS);
            if (item.length == 2) {
                map.put(item[0], item[1]);
            }
        }
        return map;
    }

    /**
     * Get the disease name in a config sms
     * @param textSms text of the received sms
     * @return name of the disease or null if not found
     */
    public static String getDiseaseForSms(String textSms) {
        textSms = HelperSms.removeAndroidIdFromString(textSms);
        if (textSms.contains(Parser.TEMPLATE_WEEKLY)) {
            return getDisease(textSms, Parser.TEMPLATE_WEEKLY, Config.KEYWORD_WEEKLY);
        } else if (textSms.contains(Parser.TEMPLATE_MONTHLY)) {
            return getDisease(textSms, Parser.TEMPLATE_MONTHLY, Config.KEYWORD_MONTHLY);
        }
        return null;
    }

    /**
     * Get the disease name in a config sms
     * @param textSms text of the received sms
     * @param templateKeyword first keyword in the sms
     * @param configKeyword corresponding keyword in the config
     * @return
     */
    private static String getDisease(String textSms, String templateKeyword, String configKeyword) {
        String disease = null;
        textSms = textSms.replace(templateKeyword + " ", "");
        textSms = textSms.replace(Parser.SEPARATOR_FIELDS, Parser.SEPARATOR_COMMA);
        String[] tab = textSms.split(" ");
        if (tab.length >= 2) {
            HashMap<String, String> values = Parser.parseFieldsForSeparator(tab[1], Parser.SEPARATOR_COMMA);
            String value;
            if ((value = findValueForKeys(values, Config.KEYS_DISEASE)) != null) {
                disease = value;
            }
        } else {
            if (BuildConfig.ERROR) {
                Log.e(TAG, "parseConfigAlert not enough parts");
            }
        }
        return disease;
    }

    /**
     * Find a key in map for different config keys
     * @param map map of key value to find in
     * @param keys list of possible matching keys
     * @return key if found, null otherwise
     */
    public static String findKeyForKeys(HashMap<String, String> map, String[] keys) {
        for (String key : keys) {
            String value = map.get(key);
            if (value != null) {
                return key;
            }
        }
        return null;
    }

    /**
     * Find a value in map for different config keys
     * @param map map of key value to find in
     * @param keys list of possible matching keys
     * @return value if found, null otherwise
     */
    public static String findValueForKeys(HashMap<String, String> map, String[] keys) {
        for (String key : keys) {
            String value = map.get(key);
            if (value != null) {
                return value;
            }
        }
        return null;
    }

    /**
     * Parse and return the servers phone number in the config message
     * @param message sms received
     * @return Set of servers phone number in the message
     */
    public static Set<String> parsePhoneNumbers(final String message) {
        String phoneNumber = HelperSms.getPhoneNumberForKey(message,"Server");
        if (!TextUtils.isEmpty(phoneNumber))
        {
            String regex  = "(\\++[0-9]+)";
            Pattern p = Pattern.compile(regex);
            Matcher m = p.matcher(message);
            Set<String> servers = new HashSet<String>();
            while(m.find()) {
                servers.add(m.group(1));
            }
            return servers;
        }
        return null;
    }
}