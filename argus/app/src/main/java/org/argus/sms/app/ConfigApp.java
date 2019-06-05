package org.argus.sms.app;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ConfigApp {



    private ConfigApp() {
    }

    public final static String SMS_CONFIG = "Android";

    public final static String SMS_OK = "ANDROID_OK";
    public final static String SMS_THRESHOLD = "ANDROID_THRESHOLD";
    public static final String CODE_OK_REPORT = "R06:";
    public static final String CODE_OK_ALERT = "R05:";
    public static final String CODE_ERROR = "R04:";
    public final static String ANDROID_SYNC_REQUEST = "ANDROID_SYNC_REQUEST";
    public static final int DEFAULT_MAX_SMS = 150;
    // Config fields
    public static final String CONF_NBR_MESSAGE = "NbMsg";
    public static final String CONF_NBR_CHAR_MAX = "NbCharMax";
    public static final String CONF_WEEKSTART = "WeekStart";
    public static final String CONF_TIMEALERT = "D4ConfAlert";
    public static final String CONF_TIMEWEEKLY= "D4ConfW";
    public static final String CONF_TIMEMONTHLY = "D4ConfM";
    public static final String CONF_MESSAGEALERT = "M4ConfAlert";
    public static final String CONF_MESSAGEWEEKLY= "M4ConfW";
    public static final String CONF_MESSAGEMONTHLY = "M4ConfM";

    public static final String ACTION_NOT_CONFIRMED = "ACTION_NOT_CONFIRMED";
    public static final String ACTION_REMINDER = "ACTION_REMINDER";

    public static final String EXTRA_TYPE = "EXTRA_TYPE";
    public static final String VERSION = "VERSION";

}
