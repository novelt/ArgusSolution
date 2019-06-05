package org.argus.sms.app.utils;

import android.content.Context;
import android.content.SharedPreferences;
import android.content.res.Configuration;
import android.preference.PreferenceManager;
import android.telephony.TelephonyManager;
import android.text.TextUtils;
import android.util.Log;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Calendar;
import java.util.Date;
import java.util.HashSet;
import java.util.List;
import java.util.Locale;
import java.util.Random;
import java.util.Set;

import fr.openium.androkit.sharedpreference.OKSharedPreferenceHelper;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.ConfigApp;
import org.argus.sms.app.R;

/**
 * Helper for preferences
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class HelperPreference {
    private final static String TAG = HelperPreference.class.getSimpleName();
    private final static boolean DEBUG = true;

    private final static String KEY_ID = "KEY_ID";
    private static final int MAX_ID = 9999;

    private final static String KEY_LAST_SYNC = "KEY_LAST_SYNC";
    private final static String KEY_LAST_SYNC_COUNT = "KEY_LAST_SYNC_COUNT";
    private final static String KEY_START_SYNC = "KEY_START_SYNC";
    private final static String KEY_CONFIG_SMS_COUNT = "KEY_CONFIG_SMS_COUNT";
    private final static String KEY_WAITING_SYNC_ID = "KEY_WAITING_SYNC_ID";
    private final static String KEY_LAST_SYNC_ID = "KEY_LAST_SYNC_ID";
    private final static String KEY_CONFIG_WEEKSTART = "KEY_CONFIG_WEEKSTART";
    private final static String KEY_CONFIG_MAXSMS = "KEY_CONFIG_MAXSMS";
    private static final String KEY_PHONES_LIST = "KEY_PHONES_LIST";
    private static final String KEY_TEST = "KEY_TEST";
    private static final String KEY_LAST_MESSAGE = "KEY_LAST_MESSAGE";
    private static final String KEY_CONFIG_ALERTMESSAGE = "KEY_CONFIG_ALERTMESSAGE";
    private static final String KEY_CONFIG_WEEKMESSAGE = "KEY_CONFIG_WEEKMESSAGE";
    private static final String KEY_CONFIG_MONTHMESSAGE = "KEY_CONFIG_MONTHMESSAGE";
    private static final String KEY_CONFIG_ALERTDELAY = "KEY_CONFIG_ALERTDELAY";
    private static final String KEY_CONFIG_WEEKDELAY = "KEY_CONFIG_WEEKDELAY";
    private static final String KEY_CONFIG_MONTHDELAY = "KEY_CONFIG_MONTHDELAY";
    private static final String KEY_CONFIG_DISPLAYPUSHCREEN = "KEY_CONFIG_DISPLAYPUSHCREEN";

    // Prefs for config via SMS
    //private static final String KEY_LAST_SMS_READ_ID = "KEY_LAST_SMS_READ_ID";
    private static final String KEY_LAST_SMS_READ_DATE = "KEY_LAST_SMS_READ_DATE";

    private static final long HOUR_IN_MS = 1000 * 60 * 60;


    /**
     * Check is the phone number are is valid. Eg in the configuration
     * @param ctx application context
     * @param phoneNumber phoneNumber to check
     * @return
     */
    public static boolean isPhoneNumberAValidServer(Context ctx, final String phoneNumber) {
        boolean validServer = false;
        Set<String> serversInConfig = new HashSet<String>();
        String server;
        if ((server = getServerPhoneNumber(ctx)) != null) {
            serversInConfig.add(server);
        }
        Set<String> prefsSet = HelperPreference.getSetServers(ctx);
        if (prefsSet != null) {
            serversInConfig.addAll(prefsSet);
        }
        //Get known gateway in numbers
        serversInConfig.addAll(HelperPreference.getServerKnownPhonesNumbers(ctx));

        for (String currentServer : serversInConfig) {
            if (currentServer.contains(phoneNumber)) {
                validServer = true;
                break;
            }
        }
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "isPhoneNumberAValidServer validServer=" + validServer);
        }
        return validServer;
    }


    /**
     * Update Application language
     * @param ctx application context
     * @param lang the language to set
     */
    public static void ChangeLanguage(Context ctx, String lang) {
        Locale locale = new Locale(lang);
        Locale.setDefault(locale);
        Configuration config = new Configuration();
        config.locale = locale;
        ctx.getResources().updateConfiguration(config, ctx.getResources().getDisplayMetrics());
    }

    /**
     * Get the server phone number
     * @param ctx application context
     * @return the phone number
     */
    public static String getServerPhoneNumber(Context ctx)
    {
        SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(ctx);
        int key = R.string.prefs_sms_gateway_out;
        if (sp.contains(ctx.getString(key))) {
            String serverNumber = sp.getString(ctx.getString(key), null);
            if (TextUtils.isEmpty(serverNumber)) {
                return null;
            } else {
                return serverNumber.trim();
            }
        }
        return null;
    }

    /**
     * Get the others gateway numbers
     *
     * @param ctx
     * @return
     */
    public static List<String> getServerKnownPhonesNumbers(Context ctx)
    {
        List<String> numbers = new ArrayList<>();

        SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(ctx);
        int key = R.string.prefs_sms_gateway_in;
        if (sp.contains(ctx.getString(key))) {
            String serverNumbers = sp.getString(ctx.getString(key), null);
            if (!TextUtils.isEmpty(serverNumbers)) {
                // Split phone numbers (remove space and split)
                numbers = Arrays.asList(serverNumbers.split("\\s*,\\s*"));
            }
        }

        return numbers;
    }

    /**
     * Get if missing of network meen no SMS
     * @param ctx application context
     * @return true if the user cannot send any sms when no network, return false otherwise
     */
    public static boolean getNetworkLock(Context ctx) {
        SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(ctx);
        int key = R.string.prefs_network;
        boolean isLocked = sp.getBoolean(ctx.getString(key), false);
        return isLocked;
    }

    /**
     * Get the prefered language
     * @param ctx application context
     * @return the current language
     */
    public static String getLanguage(Context ctx) {
        SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(ctx);
        int key = R.string.prefs_language;
        if (sp.contains(ctx.getString(key))) {
            String language = sp.getString(ctx.getString(key), null);
            if (TextUtils.isEmpty(language)) {
                return null;
            } else {
                return language;
            }
        }
        return null;
    }

    /**
     * Get the current Health Facility
     * @param ctx application context
     * @return the health facility
     */
    public static String getHFacility(Context ctx) {
        SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(ctx);
        int key = R.string.prefs_health_facility;
        if (sp.contains(ctx.getString(key))) {
            String hfacil = sp.getString(ctx.getString(key), null);
            if (TextUtils.isEmpty(hfacil)) {
                return null;
            } else {
                return hfacil;
            }
        }
        return null;
    }

    /**
     * Set the current health facility
     * @param ctx the application context
     * @param hFacility the health facility name
     */
    public static void setHFacility(Context ctx, String hFacility) {
        SharedPreferences sharedPref = PreferenceManager.getDefaultSharedPreferences(ctx);
        SharedPreferences.Editor editor = sharedPref.edit();
        editor.putString(ctx.getString(R.string.prefs_health_facility), hFacility);
        editor.commit();
    }

    /**
     * Get the current SMS id, and increment it after get
     * @param context application context
     * @return the current ID as String
     */
    public static String getSmsIdAndInc(final Context context) {
        int id = HelperPreference.getSmsId(context);
        OKSharedPreferenceHelper.saveIntInSharedPreference(context, KEY_ID, (id + 1) % MAX_ID);
        return String.valueOf(id);
    }

    /**
     * Get Sms Id to estimate the length of the SMS
     *
     * @param context
     * @return
     */
    public static int getSmsId(final Context context)
    {
       return OKSharedPreferenceHelper.getIntFromSharedPreference(context, KEY_ID, 1);
    }

    /**
     * Check if a phone number is in the preferences
     * @param context application context
     * @return true if there is a phone number in the preference, false otherwise
     */
    public static boolean isAServerIsConfigured(final Context context) {
        if (getServerPhoneNumber(context) == null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check if there is a sync in progress
     * @param context application context
     * @return true if a sync is in progress, false otherwise
     */
    public static boolean isSyncInProgress(final Context context) {
        return getSyncStartDate(context) != 0;
    }


    /**
     * Save in preference the sync start date
     * @param context application context
     * @param startDate timestamp of the startDate
     */
    public static void saveSyncStartDate(final Context context, final long startDate) {
        OKSharedPreferenceHelper.saveLongInSharedPreference(context, KEY_START_SYNC, startDate);
    }

    /**
     * Get the sync start date
     * @param context application context
     * @return start date as timestamp
     */
    public static long getSyncStartDate(final Context context) {
        return OKSharedPreferenceHelper.getLongFromSharedPreference(context, KEY_START_SYNC, 0);
    }

    /**
     * Save the last sync sms count
     * @param context application context
     * @param count timestamp of the last success sync
     */
    public static void saveLastSyncCount(final Context context, final int count) {
        OKSharedPreferenceHelper.saveIntInSharedPreference(context, KEY_LAST_SYNC_COUNT, count);
    }

    /**
     * Get the last sync sms count
     * @param context application context
     * @return the last successful sync timestamp
     */
    public static int getlastSyncCount(final Context context) {
        return OKSharedPreferenceHelper.getIntFromSharedPreference(context, KEY_LAST_SYNC_COUNT, 0);
    }

    /**
     * Save the last successful sync
     * @param context application context
     * @param lastSync timestamp of the last success sync
     */
    public static void saveLastSync(final Context context, final long lastSync) {
        OKSharedPreferenceHelper.saveLongInSharedPreference(context, KEY_LAST_SYNC, lastSync);
    }

    /**
     * Get the last successful sync timestamp
     * @param context application context
     * @return the last successful sync timestamp
     */
    public static long getlastSync(final Context context) {
        return OKSharedPreferenceHelper.getLongFromSharedPreference(context, KEY_LAST_SYNC, 0);
    }

    /**
     * Check if the sync is running for at least one hour
     * @param context application context
     * @return true if the sync is running for at least one hour, false otherwise
     */
    public static boolean isSyncRunningForMoreThanOneHour(final Context context) {
        long start = getSyncStartDate(context);
        long now = new Date().getTime();

        if ((now - start) > HOUR_IN_MS) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save the config total sms count
     * @param ctx application context
     * @param count number of sms in config
     */
    public static void saveConfigSmsCount(final Context ctx, final int count) {
        OKSharedPreferenceHelper.saveIntInSharedPreference(ctx,KEY_CONFIG_SMS_COUNT,count);
    }

    /**
     * Return the config total sms count
     * @param ctx application context
     * @return number of sms in config
     */
    public static int getConfigSmsCount(final Context ctx) {
        return OKSharedPreferenceHelper.getIntFromSharedPreference(ctx,KEY_CONFIG_SMS_COUNT, -1);
    }

    /**
     * Save the config weekstart number
     * @param ctx application context
     * @param weekStart weekstart number
     */
    public static void saveConfigWeekStart(final Context ctx, final int weekStart) {
        OKSharedPreferenceHelper.saveIntInSharedPreference(ctx,KEY_CONFIG_WEEKSTART,weekStart);
    }

    /**
     * Get the config week start
     * @param ctx application context
     * @return the weekstart number
     */
    public static int getConfigWeekStart(final Context ctx) {
        return OKSharedPreferenceHelper.getIntFromSharedPreference(ctx,KEY_CONFIG_WEEKSTART, Calendar.MONDAY);
    }

    /**
     * Save the config total sms count
     * @param ctx application context
     * @param maxSmsCharCount number of char in an SMS
     */
    public static void saveConfigSmsCharCount(final Context ctx, final int maxSmsCharCount) {
        OKSharedPreferenceHelper.saveIntInSharedPreference(ctx,KEY_CONFIG_MAXSMS,maxSmsCharCount);
    }

    /**
     * Get the config total sms count
     * @param ctx application context
     * @return the number of char in an SMS
     */
    public static int getConfigSmsCharCount(final Context ctx) {
        return OKSharedPreferenceHelper.getIntFromSharedPreference(ctx,KEY_CONFIG_MAXSMS, ConfigApp.DEFAULT_MAX_SMS);
    }

    /**
     * Save the current SMS sync id
     * @param ctx application context
     * @param syncId id for the current sync
     */
    public static void saveWaitingSyncId(final Context ctx, final int syncId) {
        OKSharedPreferenceHelper.saveIntInSharedPreference(ctx,KEY_WAITING_SYNC_ID,syncId);
    }

    /**
     * Get the current SMS sync id
     * @param ctx application context
     * @return id for the current sync
     */
    public static int getWaitingSyncId(final Context ctx) {
        return OKSharedPreferenceHelper.getIntFromSharedPreference(ctx,KEY_WAITING_SYNC_ID,-1);
    }

    /**
     * Save the last successful sync id
     * @param context application context
     * @param syncId last successful sync id
     */
    public static void saveLastSyncId(final Context context, final int syncId) {
        OKSharedPreferenceHelper.saveIntInSharedPreference(context,KEY_LAST_SYNC_ID,syncId);
    }

    /**
     * Get the last sync id
     * @param ctx application context
     * @return last successful sync id
     */
    public static int getLastSyncId(final Context ctx) {
        return OKSharedPreferenceHelper.getIntFromSharedPreference(ctx,KEY_LAST_SYNC_ID,-1);
    }

    /**
     * Save the sms servers set
     * @param ctx application context
     * @param phones Set of servers to save in preference
     */
    public static void saveSetServers(final Context ctx, final Set<String> phones) {
        OKSharedPreferenceHelper.saveStringSet(ctx, KEY_PHONES_LIST, phones);
    }

    /**
     * Get the sms servers set
     * @param ctx application context
     * @return sms servers set
     */
    public static Set<String> getSetServers(final Context ctx) {
        return OKSharedPreferenceHelper.loadStringSet(ctx,KEY_PHONES_LIST);
    }

    /**
     * Save the last received message
     * @param ctx application context
     * @param lastMessage last received message
     */
    public static void saveLastMessage(final Context ctx, final String lastMessage) {
        OKSharedPreferenceHelper.saveStringInSharedPreference(ctx, KEY_LAST_MESSAGE, lastMessage);
    }

    /**
     * Get the last received message
     * @param ctx application context
     * @return last received message
     */
    public static String getLastMessage(final Context ctx) {
        return OKSharedPreferenceHelper.getStringFromSharedPreference(ctx, KEY_LAST_MESSAGE, null);
    }

    /**
     * Save the Config alert message
     * @param ctx application context
     * @param alertMessage alert message to save
     */
    public static void saveAlertMessage(final Context ctx, final String alertMessage) {
        OKSharedPreferenceHelper.saveStringInSharedPreference(ctx, KEY_CONFIG_ALERTMESSAGE, alertMessage);
    }

    /**
     * Get the config alert message
     * @param ctx application context
     * @return alert message
     */
    public static String getAlertMessage(final Context ctx) {
        return OKSharedPreferenceHelper.getStringFromSharedPreference(ctx, KEY_CONFIG_ALERTMESSAGE, null);
    }

    /**
     * Save the Config week message
     * @param ctx application context
     * @param weekMessage week message to save
     */
    public static void saveWeekMessage(final Context ctx, final String weekMessage) {
        OKSharedPreferenceHelper.saveStringInSharedPreference(ctx, KEY_CONFIG_WEEKMESSAGE, weekMessage);
    }

    /**
     * Get the config week message
     * @param ctx application context
     * @return week message
     */
    public static String getWeekMessage(final Context ctx) {
        return OKSharedPreferenceHelper.getStringFromSharedPreference(ctx, KEY_CONFIG_WEEKMESSAGE, null);
    }

    /**
     * Save the Config month message
     * @param ctx application context
     * @param monthMessage month message to save
     */
    public static void saveMonthMessage(final Context ctx, final String monthMessage) {
        OKSharedPreferenceHelper.saveStringInSharedPreference(ctx, KEY_CONFIG_MONTHMESSAGE, monthMessage);
    }

    /**
     * Get the config month message
     * @param ctx application context
     * @return month message
     */
    public static String getMonthMessage(final Context ctx) {
        return OKSharedPreferenceHelper.getStringFromSharedPreference(ctx, KEY_CONFIG_MONTHMESSAGE, null);
    }

    /**
     * Save the alert delay
     * @param context application context
     * @param delay delay alert
     */
    public static void saveAlertDelay(final Context context, final int delay) {
        OKSharedPreferenceHelper.saveIntInSharedPreference(context,KEY_CONFIG_ALERTDELAY,delay);
    }

    /**
     * Get the alert delay
     * @param ctx application context
     * @return the saved alert delay
     */
    public static int getAlertDelay(final Context ctx) {
        return OKSharedPreferenceHelper.getIntFromSharedPreference(ctx,KEY_CONFIG_ALERTDELAY,-1);
    }

    /**
     * Save the week delay
     * @param context application context
     * @param delay delay week
     */
    public static void saveWeekDelay(final Context context, final int delay) {
        OKSharedPreferenceHelper.saveIntInSharedPreference(context,KEY_CONFIG_WEEKDELAY,delay);
    }

    /**
     * Get the week delay
     * @param ctx application context
     * @return the saved week delay
     */
    public static int getWeekDelay(final Context ctx) {
        return OKSharedPreferenceHelper.getIntFromSharedPreference(ctx,KEY_CONFIG_WEEKDELAY,-1);
    }

    /**
     * Save the month delay
     * @param context application context
     * @param delay delay month
     */
    public static void saveMonthDelay(final Context context, final int delay) {
        OKSharedPreferenceHelper.saveIntInSharedPreference(context,KEY_CONFIG_MONTHDELAY,delay);
    }

    /**
     * Get the month delay
     * @param ctx application context
     * @return the saved month delay
     */
    public static int getMonthDelay(final Context ctx) {
        return OKSharedPreferenceHelper.getIntFromSharedPreference(ctx,KEY_CONFIG_MONTHDELAY,-1);
    }

    /**
     * Clear all current sync data
     * @param context application context
     */
    public static void clearCurrentSyncData(final Context context) {
        HelperPreference.saveSyncStartDate(context, 0);
        HelperPreference.saveWaitingSyncId(context, -1);
    }

    public static void saveTextToDisplayInPushScreen(final Context ctx, final String text) {
        OKSharedPreferenceHelper.saveStringInSharedPreference(ctx, KEY_CONFIG_DISPLAYPUSHCREEN, text);
    }

    public static String getTextToDisplayInPushScreen(final Context ctx) {
        return OKSharedPreferenceHelper.getStringFromSharedPreference(ctx, KEY_CONFIG_DISPLAYPUSHCREEN, null);
    }

    public static String getSecurityKey(final Context ctx)
    {
        return OKSharedPreferenceHelper.getStringFromSharedPreference(ctx, ctx.getString(R.string.prefs_config_encrypt_security_key), "");
    }

    public static long getLastSMSRead(final Context ctx)
    {
        return OKSharedPreferenceHelper.getLongFromSharedPreference(ctx, KEY_LAST_SMS_READ_DATE, 0);
    }

    public static void setLastSMSRead(final Context ctx, long smsId)
    {
        OKSharedPreferenceHelper.saveLongInSharedPreference(ctx, KEY_LAST_SMS_READ_DATE, smsId);
    }

    public static String getUniqueDeviceNumber(Context context)
    {
        TelephonyManager tm = (TelephonyManager) context.getSystemService(Context.TELEPHONY_SERVICE);
        String uniqueDeviceNumber;

        // At boot IMEI might not be available
        int tries = 30;
        // Just wait a bit for the IMEI to be available.
        do {
            try {
                uniqueDeviceNumber = tm.getDeviceId();

                if ((uniqueDeviceNumber == null) || "".equals(uniqueDeviceNumber) ||
                        uniqueDeviceNumber.contains("*") || uniqueDeviceNumber.contains("000000000000000") || (uniqueDeviceNumber.length() < 15)) {
                    uniqueDeviceNumber = null;
                    Thread.sleep(100); // sleep for a 1/10 second
                } else {
                    break;
                }

            } catch (Exception e) {
                Log.e(TAG, "", e);
                uniqueDeviceNumber = null;
            }

        } while (--tries > 0);

        return uniqueDeviceNumber;
    }

    public static boolean isConfigBySMSEnabled(final Context ctx)
    {
        return OKSharedPreferenceHelper.getBooleanFromSharedPreference (ctx, ctx.getString(R.string.prefs_config_sms_enable), true);
    }

    public static boolean isOnlyDefinedGatewayEnabled(final Context ctx)
    {
        return OKSharedPreferenceHelper.getBooleanFromSharedPreference (ctx, ctx.getString(R.string.prefs_config_only_known_gateway), true);
    }

    public static boolean isExternalArgusAlertEnabled(final Context ctx)
    {
        return OKSharedPreferenceHelper.getBooleanFromSharedPreference(ctx, ctx.getString(R.string.prefs_config_alert_external), false);
    }
}
