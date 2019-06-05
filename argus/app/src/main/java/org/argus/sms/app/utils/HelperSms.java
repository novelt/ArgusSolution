package org.argus.sms.app.utils;

import android.content.ContentResolver;
import android.content.ContentValues;
import android.content.Context;
import android.content.pm.PackageInfo;
import android.database.Cursor;
import android.net.Uri;
import android.text.TextUtils;
import android.util.Log;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Date;
import java.util.LinkedList;
import java.util.List;
import java.util.Set;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import hugo.weaving.DebugLog;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.ConfigApp;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.provider.SesContract;

/**
 * Utility class for Sms
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class HelperSms {

    private final static String TAG = HelperSms.class.getSimpleName();
    private final static boolean DEBUG = true;
    private final static boolean VERBOSE = true;


    /**
     * Save the sms into the application database
     *
     * @param context Application context
     * @param message sms message to save
     * @return TypeSms saved into database, null if insert not possible
     */
    public static TypeSms saveSmsToDatabase(Context context, String message)
    {
        TypeSms type = null;
        if (Parser.isConfig(message)) {
            type = TypeSms.CONFIG;
            saveSmsConfigToDatabase(context.getContentResolver(), message);
        } else {
            type = Parser.getTypeFromText(message, Config.getInstance(context));
            if (!updateIfIdAlreadyExist(context, message) || isMessageThreshold(message) || isMessageReceivedButNotOk(message)) {
                long timestamp = 0;
                if (isMessageThreshold(message) || isMessageReceivedButNotOk(message)) {
                    // get the timestamp of the corresponding report
                    String id = getIdInString(message);
                    String reportId = getReportIdInString(message);
                    String[] projection = {SesContract.Sms._ID, SesContract.Sms.TIMESTAMP};
                    String selection = SesContract.Sms.ID + "=?";

                    List<String> selectionArgsList = new ArrayList<>() ;
                    selectionArgsList.add(id);

                    // try to get the RID send back by the server
                    if (reportId != null) {
                        selection += " AND " + SesContract.Sms.REPORTID + "=?";
                        selectionArgsList.add(String.valueOf(reportId));
                    }

                    String[] selectionArgs = new String[selectionArgsList.size()];
                    selectionArgsList.toArray(selectionArgs);

                    String sortOrder = SesContract.Sms.SENDDATE + " DESC, " + SesContract.Sms.TIMESTAMP + " DESC";

                    Cursor c = context.getContentResolver().query(SesContract.Sms.CONTENT_URI, projection, selection, selectionArgs, sortOrder);

                    // get the text without all the keywords
                    // remove the Android ID
                    message = removeAndroidIdFromString(message);
                    // remove the THRESHOLD
                    message = removeWordFromString(message, ConfigApp.SMS_THRESHOLD);
                    // remove the OK
                    message = removeWordFromString(message, ConfigApp.SMS_OK);

                    if (c.moveToFirst()) {
                        timestamp = c.getLong(c.getColumnIndex(SesContract.Sms.TIMESTAMP));
                        // make it display just before the report
                        timestamp++;
                        // set the subtype as threshold
                        long uid = c.getLong(c.getColumnIndex(SesContract.Sms._ID));
                        ContentValues cv = new ContentValues();
                        cv.put(SesContract.Sms.SUBTYPE, SubTypeSms.THRESHOLD.toInt());
                        cv.put(SesContract.Sms.SMSCONFIRM, message);

                        context.getContentResolver().update(SesContract.Sms.buildBaseIdUri(uid), cv, null, null);
                    } else {
                        if (BuildConfig.ERROR) {
                            Log.e(TAG, "saveSmsToDatabase no data");
                        }
                        // display it now
                        timestamp = new Date().getTime();
                    }
                    c.close();

                } else {
                    timestamp = new Date().getTime();
                }
                Sms sms = Parser.getSmsFromText(message, Config.getInstance(context));
                ContentValues cv = sms.getContentValues(Config.getInstance(context));
                cv.put(SesContract.Sms.TEXT, message);
                cv.put(SesContract.Sms.TYPE, type.toInt());
                cv.put(SesContract.Sms.SUBTYPE, Parser.getSubType(message).toInt());
                cv.put(SesContract.Sms.TIMESTAMP, timestamp);

                context.getContentResolver().insert(SesContract.Sms.CONTENT_URI, cv);
            }
        }

        return type;
    }

    /**
     * Update the database for the message id with the content of the message
     *
     * @param ctx     application context
     * @param message sms received
     * @return true if updated, false otherwise
     */
    @DebugLog
    public static boolean updateIfIdAlreadyExist(final Context ctx, String message)
    {
        boolean updated = false;
        String id = getIdInString(message);
        String reportId = getReportIdInString(message);
        if (id == null) {
            updated = false;
        } else {
            ContentValues cv = new ContentValues();
            if (isMessageOk(message) || isMessageThreshold(message)) {
                cv.put(SesContract.Sms.STATUS, Status.RECEIVED.toInt());
            } else if (isMessageReceivedButNotOk(message)) {
                cv.put(SesContract.Sms.STATUS, Status.RECEIVED_BUT_NOT_OK.toInt());
            } else {
                cv.put(SesContract.Sms.STATUS, Status.ERROR.toInt());
            }
            // in all case, save the confirm message
            message = HelperSms.removeAndroidIdFromString(message);
            message = HelperSms.removeWordFromString(message, ConfigApp.SMS_OK);
            cv.put(SesContract.Sms.SMSCONFIRM, message);
            String selection = SesContract.Sms.ID + "=?";

            List<String> selectionArgsList = new ArrayList<>() ;
            selectionArgsList.add(String.valueOf(id));

            // try to get the RID send back by the server
            if (reportId != null) {
                selection += " AND " + SesContract.Sms.REPORTID + "=?";
                selectionArgsList.add(String.valueOf(reportId));
            }

            String[] selectionArgs = new String[selectionArgsList.size()];
            selectionArgsList.toArray(selectionArgs);

            String sortOrder = SesContract.Sms.SENDDATE + " DESC, " + SesContract.Sms.TIMESTAMP + " DESC";

            int count = ctx.getContentResolver().update(SesContract.Sms.CONTENT_URI, cv, selection, selectionArgs);
            if (count > 0) {
                updated = true;
                Cursor c = ctx.getContentResolver().query(SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, sortOrder);
                if (c != null && c.moveToFirst()) {
                    long _id = c.getLong(c.getColumnIndex(SesContract.Sms._ID));
                    HelperReminder.clearReminderNotConfirmed(ctx, SesContract.Sms.buildBaseIdUri(_id));
                }
                if (c != null) {
                    c.close();
                }
            }
        }
        return updated;
    }

    /**
     * Get the id in the message
     *
     * @param message sms received
     * @return the id or null if not found
     */
    @DebugLog
    public static String getIdInString(final String message)
    {
        return getValueForKeywordInString(message, Config.KEYWORD_ID);
    }

    /**
     * Get the id in the message
     *
     * @param message sms received
     * @return the id or null if not found
     */
    @DebugLog
    public static String getReportIdInString(final String message)
    {
        return getValueForKeywordInString(message, Config.KEYWORD_REPORT_ID);
    }

    /**
     * Find the value for a specific keyword in String
     *
     * @param message sms received
     * @param keyword keyword to find in message
     * @return the value if found, null otherwise
     */
    public static String getValueForKeywordInString(final String message, final String keyword)
    {
        if (TextUtils.isEmpty(message)) {
            return null;
        }
        String regex = ".*" + keyword + "=(\\d+).*";
        Pattern p = Pattern.compile(regex);
        Matcher m = p.matcher(message);
        if (m.matches()) {
            return m.group(1);
        }
        return null;
    }

    /**
     * Get all phone numbers for a specific key
     *
     * @param message message look into
     * @param keyword keyword for phone numbers
     * @return list of phone numbers, null if not exist
     */
    public static String getPhoneNumberForKey(final String message, final String keyword) {
        if (TextUtils.isEmpty(message)) {
            return null;
        }
        String regex = ".*" + keyword + "=([+0-9]+).*";
        Pattern p = Pattern.compile(regex);
        Matcher m = p.matcher(message);
        if (m.matches()) {
            return m.group(1);
        }
        return null;
    }

    /**
     * Check if message is ok
     *
     * @param message sms received
     * @return true if ok, false otherwise
     */
    public static boolean isMessageOk(final String message) {
        return (message.contains(ConfigApp.SMS_OK) || message.contains(ConfigApp.CODE_OK_ALERT)) && !message.contains(ConfigApp.CODE_ERROR);
    }

    /**
     * Check if message is received but not ok
     *
     * @param message sms received
     * @return true if received but not ok, false otherwise
     */
    public static boolean isMessageReceivedButNotOk(final String message) {
        return message.contains(ConfigApp.SMS_OK) && message.contains(ConfigApp.CODE_ERROR);
    }

    /**
     * Check if message is a threshold
     *
     * @param message sms received
     * @return true if message is a threshold, false otherwise
     */
    public static boolean isMessageThreshold(final String message) {
        return message.contains(ConfigApp.SMS_THRESHOLD);
    }

    /**
     * Check if message contains a sync request
     *
     * @param message sms received
     * @return true if the message contains a sync request, false otherwise
     */
    public static boolean isMessageContainsSyncRequest(final String message) {
        return isMessageContains(message, ConfigApp.ANDROID_SYNC_REQUEST);
    }

    /**
     * Check if a message contains a specific value
     * case insensitive check
     *
     * @param sms   sms received
     * @param value value to look into
     * @return true if the message contains the value (case insensitive check), false otherwise
     */
    private static boolean isMessageContains(final String sms, final String value) {
        return sms.toLowerCase().contains(value.toLowerCase());
    }


    /**
     * Save sms config into database
     *
     * @param resolver Content resolver of the application
     * @param message  sms received
     * @return
     */
    public static boolean saveSmsConfigToDatabase(ContentResolver resolver, String message) {
        ContentValues cv = new ContentValues();

        Integer type = Parser.getConfigType(message).toInt();
        Integer subtype = Parser.getSubType(message).toInt();
        String disease = Parser.getDiseaseForSms(message);

        cv.put(SesContract.Sms.TYPE, type);
        cv.put(SesContract.Sms.SUBTYPE, subtype);
        cv.put(SesContract.Sms.DISEASE, disease);
        String id = HelperSms.getIdInString(message);
        if (id != null) {
            cv.put(SesContract.Sms.ID, id);
        }
        message = HelperSms.removeAndroidIdFromString(message);
        cv.put(SesContract.Sms.TIMESTAMP, new Date().getTime());

        // If all these field are valid, check if an sms for the same disease is already in the database
        if ((disease != null || subtype == SubTypeSms.MODEL_ALERT.toInt()) && id != null && type != null && subtype != null) {
            String selection = "";
            Cursor cursor;
            if (disease != null) { // If it is a disease
                selection = SesContract.Sms.ID + "=? AND " + SesContract.Sms.DISEASE + "=? AND " + SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.SUBTYPE + "=?";
                String[] selectionArgs = {id, disease, type.toString(), subtype.toString()};
                cursor = resolver.query(SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, null);
            } else { // If it is an alert
                selection = SesContract.Sms.ID + "=? AND " + SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.SUBTYPE + "=?";
                String[] selectionArgs = {id, type.toString(), subtype.toString()};
                cursor = resolver.query(SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, null);
            }
            if (cursor != null && cursor.getCount() > 0 && cursor.moveToFirst()) {
                String text = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));
                long uid = cursor.getLong(cursor.getColumnIndex(SesContract.Sms._ID));
                if (text != null) {
                    if (disease != null)
                        text = concatTwoConfigDiseaseSms(text, message);
                    else
                        text = concatTwoConfigAlertSms(text, message);
                    cv.put(SesContract.Sms.TEXT, text);
                    resolver.update(SesContract.Sms.buildBaseIdUri(uid), cv, null, null);
                    return true;
                }
            }
            if (cursor != null) {
                try { cursor.close(); }
                catch (Exception e) {
                    e.printStackTrace();
                }
            }
        }

        cv.put(SesContract.Sms.TEXT, message);
        resolver.insert(SesContract.Sms.CONTENT_URI, cv);
        return true;
    }

    /**
     * Method called to concat 2 sms for the same report and disease.
     * this method will remove all duplicate field in the two sms before the concatenation
     * @param orignal is the text message already in the database (String)
     * @param lastReceived is the message to append at the original (String)
     * @return a string, the result of the concatenation
     */
    public static String concatTwoConfigDiseaseSms(String orignal, String lastReceived) {
        String[] originalSplited = orignal.split(Parser.SEPARATOR_FIELDS);
        // Create LinkedList because List create with Arrays.asList have a fixed size
        List<String> lastReceivedSplited = new LinkedList<String>(Arrays.asList(lastReceived.split(Parser.SEPARATOR_FIELDS)));

        StringBuilder sb = new StringBuilder();

        // Remove all duplicate field.
        for (String field : originalSplited) {
            if (lastReceivedSplited.contains(field))
                lastReceivedSplited.remove(field);
        }
        sb.append(orignal);

        // If there is field in the new message that wasn't in the previous, append it.
        if (!lastReceivedSplited.isEmpty()) {
            for (String field : lastReceivedSplited) {
                sb.append(Parser.SEPARATOR_FIELDS);
                sb.append(field);
            }
        }

        return sb.toString();
    }

    /**
     * Method called to concat 2 sms for the same Alert.
     * this method will remove all duplicate field in the two sms before the concatenation
     * @param orignal is the text message already in the database (String)
     * @param lastReceived is the message to append at the original (String)
     * @return a string, the result of the concatenation
     */
    public static String concatTwoConfigAlertSms(String orignal, String lastReceived) {
        String[] originalSplited = orignal.split(Parser.SEPARATOR_FIELDS);
        // Create LinkedList because List create with Arrays.asList have a fixed size
        List<String> lastReceivedSplited = new LinkedList<String>(Arrays.asList(lastReceived.split(Parser.SEPARATOR_FIELDS)));

        // Remove TEMPLATE-ALERT: ALERT form the second message
        if (lastReceivedSplited.get(0) != null
                && lastReceivedSplited.get(0).contains(Config.KEYWORD_ALERT)
                && lastReceivedSplited.get(0).contains(Parser.TEMPLATE_ALERT)) {
            lastReceivedSplited.set(0, lastReceivedSplited.get(0).replace(Parser.TEMPLATE_ALERT, "").replace(Config.KEYWORD_ALERT, ""));
            // Clean Spaces
            while (lastReceivedSplited.get(0).getBytes().length > 0 && lastReceivedSplited.get(0).getBytes()[0] == ' ')
                lastReceivedSplited.set(0, lastReceivedSplited.get(0).replaceFirst(" ", ""));
        }

        StringBuilder sb = new StringBuilder();

        // Remove all duplicate field.
        for (String field : originalSplited) {
            if (lastReceivedSplited.contains(field))
                lastReceivedSplited.remove(field);
        }
        sb.append(orignal);

        // If there is field in the new message that wasn't in the previous, append it.
        if (!lastReceivedSplited.isEmpty()) {
            for (String field : lastReceivedSplited) {
                sb.append(Parser.SEPARATOR_FIELDS);
                sb.append(field);
            }
        }

        return sb.toString();
    }

    /**
     * Parse message if it's a config and save it into the preferences
     *
     * @param ctx
     * @param message
     * @return
     */
    public static boolean parseSmsIfConfig(Context ctx, String message) {
        boolean isConfig = Parser.isConfig(message);
        if (isConfig) {
            TypeSms type = Parser.getConfigType(message);
            SubTypeSms subType = Parser.getSubType(message);
            if (type == TypeSms.CONFIG && subType == SubTypeSms.MODEL_NONE) {
                String smsCount = getValueForKeywordInString(message, ConfigApp.CONF_NBR_MESSAGE);
                if (smsCount != null) {
                    int intSmsCount = Integer.parseInt(smsCount);
                    HelperPreference.saveConfigSmsCount(ctx, intSmsCount);
                    OttoSingleton.getInstance().getBus().post(new EventConfigSmsCount(intSmsCount));
                }
                String maxChar = getValueForKeywordInString(message, ConfigApp.CONF_NBR_CHAR_MAX);
                HelperPreference.saveConfigSmsCharCount(ctx, Integer.parseInt(maxChar));
                String weekStartString = getValueForKeywordInString(message, ConfigApp.CONF_WEEKSTART);
                // the epidemiological week stat with Monday as 1 and sunday as 7
                int weekStartInt = Integer.parseInt(weekStartString);
                // change it to the Java week (monday as 2 and sunday as 1)
                weekStartInt = (weekStartInt + 1) % 7;
                HelperPreference.saveConfigWeekStart(ctx, weekStartInt);

                // delays
                String delayAlert = getValueForKeywordInString(message, ConfigApp.CONF_TIMEALERT);
                if (!TextUtils.isEmpty(delayAlert)) {
                    int value = Integer.parseInt(delayAlert);
                    HelperPreference.saveAlertDelay(ctx, value);
                }
                // ------------------------------------------------------------
                String delayWeekly = getValueForKeywordInString(message, ConfigApp.CONF_TIMEWEEKLY);
                if (!TextUtils.isEmpty(delayWeekly)) {
                    int value = Integer.parseInt(delayWeekly);
                    HelperPreference.saveWeekDelay(ctx, value);
                }
                // ------------------------------------------------------------
                String delayMonthly = getValueForKeywordInString(message, ConfigApp.CONF_TIMEMONTHLY);
                if (!TextUtils.isEmpty(delayWeekly)) {
                    int value = Integer.parseInt(delayMonthly);
                    HelperPreference.saveMonthDelay(ctx, value);
                }
                // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

                Set<String> phones = Parser.parsePhoneNumbers(message);
                if (phones != null)
                    HelperPreference.saveSetServers(ctx, phones);
            } else if (type == TypeSms.CONFIG && subType == SubTypeSms.MODEL_ALERT) {
                String messageAlert = getConfMessageForKey(ConfigApp.CONF_MESSAGEALERT, message);
                HelperPreference.saveAlertMessage(ctx, messageAlert);
            } else if (type == TypeSms.CONFIG && subType == SubTypeSms.MODEL_WEEKLY) {
                String messageWeek = getConfMessageForKey(ConfigApp.CONF_MESSAGEWEEKLY, message);
                HelperPreference.saveWeekMessage(ctx, messageWeek);
            } else if (type == TypeSms.CONFIG && subType == SubTypeSms.MODEL_MONTHLY) {
                String messageMonth = getConfMessageForKey(ConfigApp.CONF_MESSAGEMONTHLY, message);
                HelperPreference.saveMonthMessage(ctx, messageMonth);
            } else if (type == TypeSms.CONFIG && subType == SubTypeSms.MODEL_HEALTHFACILITY) {
                Config.getInstance(ctx).loadConfigForSms(message, ctx);
                Set<String> phones = Parser.parsePhoneNumbers(message);
                if (phones != null)
                    HelperPreference.saveSetServers(ctx, phones);
            }
        }
        return isConfig;
    }

    /**
     * Get the number of config and model message received
     *
     * @param context context of the application
     * @return count of messages
     */
    public static int getSmsConfigAndModelCount(Context context) {
        int count = 0;
        String selection = getSmsConfigAndModelCountSelection();
        String[] selectionArgs = getSmsConfigAndModelCountSelectionArgs(context);
        Cursor cursor = context.getContentResolver().query(SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, null);

        if (cursor != null) {
            if (cursor.moveToFirst())
                count = cursor.getCount();
            cursor.close();
        } else {
            count = 0;
        }

        return count;
    }

    // Following code deleted , asked by Guerra José (RE: Nouvelle version ARGUS : sent 05 May 2017 18:36)
    /**
     * Get List of SMS from History data
     *
     * @param context
     * @param type
     * @param week
     * @param month
     * @param year
     * @param timestamp
     * @param errorOnly
     * @return
     */
    /*
    public static ArrayList<Sms> getListOfSmsFromHistory(Context context, TypeSms type, String week, String month, String year, String timestamp, boolean errorOnly)
    {
        ArrayList<Sms> list = new ArrayList<>();
        ArrayList<String> args = new ArrayList<>();

        String selection = SesContract.Sms.TIMESTAMP + "=? AND " + SesContract.Sms.TYPE + "=? ";
        args.add(timestamp);
        args.add(String.valueOf(type.toInt()));

        switch (type) {
            case WEEKLY:
                selection += " AND " + SesContract.Sms.YEAR + "=? AND " + SesContract.Sms.WEEK + "=? ";
                args.add(year);
                args.add(week) ;
                break;
            case MONTHLY:
                selection += " AND " + SesContract.Sms.YEAR + "=? AND " + SesContract.Sms.MONTH + "=? ";
                args.add(year);
                args.add(month) ;
                break ;
        }

        // Only SMS with status = ERROR
        if (errorOnly) {
            selection += " AND " + SesContract.Sms.STATUS + "=? ";
            args.add(String.valueOf(Status.ERROR.toInt())) ;
        }

        Cursor cursor = context.getContentResolver().query(SesContract.Sms.CONTENT_URI, null, selection, args.toArray(new String[0]), null);

        if (cursor != null && cursor.moveToFirst()) {
            do {
                list.add(new Sms(Config.getInstance(context), cursor));
            } while (cursor.moveToNext());
        }

        if (cursor != null) {
            cursor.close();
        }


        return list ;
    }
    */

    /**
     * Get the selection clause for config, model and selection
     *
     * @return Selection clause
     */
    public static String getSmsConfigAndModelCountSelection() {
        return getSmsConfigAndModelCountSelection(true);
    }

    /**
     * Get the selection clause for config, model and selection
     *
     * @param equals true if including current sync, false without current sync
     * @return
     */
    public static String getSmsConfigAndModelCountSelection(boolean equals) {
        String selection = "(" + SesContract.Sms.TYPE + "=? OR " + SesContract.Sms.TYPE + "=?) AND " + SesContract.Sms.ID;
        selection = selection + (equals ? "=?" : "!=?");
        return selection;
    }

    /**
     * Get selection args for config and model
     *
     * @param context
     * @return
     */
    public static String[] getSmsConfigAndModelCountSelectionArgs(Context context) {
        String[] selectionArgs = {String.valueOf(TypeSms.MODEL.toInt()), String.valueOf(TypeSms.CONFIG.toInt()), String.valueOf(HelperPreference.getWaitingSyncId(context))};
        return selectionArgs;
    }

    /**
     * Get the final {@link Status} for a line in history for multiple status of sent report
     *
     * @param statusList status list from report
     * @return display {@link Status}
     */
    public static Status getStatusFromStatusList(String statusList) {
        Status status = Status.UNKNOWN;
        if (!TextUtils.isEmpty(statusList)) {
            String[] parts = statusList.split(",");
            Status[] listStatus = new Status[parts.length];
            for (int i = 0; i < parts.length; i++) {
                listStatus[i] = Status.fromInt(Integer.parseInt(parts[i]));
            }
            status = Status.SENT;
            for (Status s : listStatus) {
                if (s == Status.ERROR || s == Status.RECEIVED_BUT_NOT_OK) {
                    return s;
                }
            }
            int sentcount = 0;
            int receivedCount = 0;
            for (Status s : listStatus) {
                if (s == Status.SENT) {
                    sentcount++;
                } else if (s == Status.RECEIVED || s == Status.RECEIVED_BUT_NOT_OK) {
                    receivedCount++;
                }
            }

            if (receivedCount == listStatus.length) {
                return Status.RECEIVED;
            }

            if (receivedCount > 0) {
                return Status.PARTIAL;
            }

        }
        return status;
    }

    /**
     * Remove the Android id and the Report Id from a String
     *
     * @param text message
     * @return String without the ID
     */
    public static String removeAndroidIdFromString(String text) {
        text = HelperSms.removeKeyWordIdFromString(Config.KEYWORD_REPORT_ID, text);
        text = HelperSms.removeKeyWordIdFromString(Config.KEYWORD_ID, text);

        return text;
    }

    /**
     * Remove the KeyWord Id from a String
     *
     * @param keyWord
     * @param text
     * @return
     */
    private static String removeKeyWordIdFromString(String keyWord, String text)
    {
        text = text.replaceAll(keyWord + "=\\d+\\s?", "");
        if (text.endsWith(" , ")) {
            text = text.substring(0, text.length() - 3);
        } else if (text.endsWith(", ")) {
            text = text.substring(0, text.length() - 2);
        } else if (text.endsWith(",")) {
            text = text.substring(0, text.length() - 1);
        }
         // Case when Server return message unexpected keyword . xxxx [ content ]
        else if  (text.endsWith(" , ]")) {
            text = text.substring(0, text.length() - 4) + "]";
        } else if  (text.endsWith(", ]")) {
            text = text.substring(0, text.length() - 3) + "]";
        } else if  (text.endsWith(",]")) {
            text = text.substring(0, text.length() - 2) + "]";
        }

        return text;
    }

    /**
     * Remove a keyword and the associated number value from a string
     *
     * @param text    text to process
     * @param keyword keyword to remove
     * @return
     */
    public static String removeKeywordAndNumberFromString(String text, String keyword) {
        String regex = "\\s?" + keyword + "=\\d+\\s?,?";
        text = text.replaceAll(regex, "");
        return text;
    }


    /**
     * Remove the word from a String
     *
     * @param text         message
     * @param wordToRemove word to remove in the message
     * @return String without the ID
     */
    public static String removeWordFromString(String text, String wordToRemove) {
        text = text.replaceAll(wordToRemove, "");
        return text;
    }


    /**
     * Remove the first word from a String
     *
     * @param text message
     * @return String first word or the complete message if there were no space
     */
    public static String removeFirstWordInString(final String text) {
        if (!TextUtils.isEmpty(text)) {
            String[] list = text.split(" ", 2);
            if (list.length >= 2) {
                return list[1];
            }
        }
        return text;
    }

    /**
     * Remove the first keyword in a String
     *
     * @param text remove the first keyword in the string
     * @return the text without the first keyword
     */
    public static String removeFirstKeywordInString(String text) {
        if (!TextUtils.isEmpty(text)) {
            text = text.replaceFirst("[^:]+:\\s+", "");
        }
        return text;
    }

    /**
     * Send a sync request to the server and save the status
     *
     * @param context context of the application
     */
    public static void sendSyncRequest(final Context context, final HelperSmsSender.SmsListener listener) {
        HelperPreference.saveLastSyncCount(context, 0);
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "sendSyncRequest ");
        }
        final String sms = ConfigApp.SMS_CONFIG + " " + Config.KEYWORD_ID + "=%s , " + ConfigApp.VERSION + "=%s";
        String version;
        try {
            PackageInfo pInfo = context.getPackageManager().getPackageInfo(context.getPackageName(), 0);
            version = pInfo.versionName;
        } catch (Exception e){
            version = "0";
        }
        final String id = HelperPreference.getSmsIdAndInc(context);
        final String format = String.format(sms, id, version);
        HelperSmsSender.sendSms(context, format, new HelperSmsSender.SmsListener<Sms>() {
            @Override
            public void onSmsSentSuccess(final String tag, final Sms sms) {
                HelperPreference.saveWaitingSyncId(context, Integer.parseInt(id));
                HelperPreference.saveSyncStartDate(context, new Date().getTime());
                if (listener != null){
                    listener.onSmsSentSuccess(tag, sms);
                }
            }

            @Override
            public void onSmsSentError(final String tag, final Sms sms, final int error) {
                HelperSmsSender.displayStandardErrorText(context);
                if (listener != null){
                    listener.onSmsSentError(tag, sms, error);
                }
            }
        }, "sync", null);

    }

    /**
     * Resend an SMS and update it's status to sent
     *
     * @param context          context of the application
     * @param typeSms          type of sms
     * @param cursorAtPosition cursor moved at position
     */
    public static void resendSmsAndUpdateStatus(final Context context, final TypeSms typeSms, final Cursor cursorAtPosition)
    {
        Sms sms = new Sms(Config.getInstance(context), cursorAtPosition);
        HelperSmsSender.sendSms(context, typeSms, sms, new HelperSmsSender.SmsListener<Sms>() {
            @Override
            public void onSmsSentSuccess(final String tag, final Sms sms) {
                int uid = cursorAtPosition.getInt(cursorAtPosition.getColumnIndex(SesContract.Sms._ID));
                Uri uri = SesContract.Sms.buildBaseIdUri(uid);
                ContentValues cv = new ContentValues();
                sms.mStatus = Status.SENT;
                cv.put(SesContract.Sms.STATUS, sms.mStatus.toInt());
                sms.mSendDate = new Date().getTime();
                cv.put(SesContract.Sms.SENDDATE, sms.mSendDate);

                new UpdateAsyncQueryHandler(context.getContentResolver()).startUpdate(789, null, uri, cv, null, null);
            }

            @Override public void onSmsSentError(final String tag, final Sms sms, final int error) {
                HelperSmsSender.displayStandardErrorText(context);
            }

        }, "sms");

    }

    /**
     * Resend an SMS and update it's status to sent
     *
     * @param context          context of the application
     * @param typeSms          type of sms
     * @param cursor           cursor
     * @param pos              the cursor position
     */
    public static void resendSmsAndUpdateStatus(final Context context, final TypeSms typeSms, final Cursor cursor, final int pos)
    {
        cursor.moveToPosition(pos);
        Sms sms = new Sms(Config.getInstance(context), cursor);
        HelperSmsSender.sendSms(context, typeSms, sms, new HelperSmsSender.SmsListener<Sms>() {
            @Override
            public void onSmsSentSuccess(final String tag, final Sms sms) {
                //cursor.moveToPosition(pos);
                //int uid = cursor.getInt(cursor.getColumnIndex(SesContract.Sms._ID));
                Uri uri = SesContract.Sms.buildBaseIdUri(sms._Id);
                ContentValues cv = new ContentValues();
                sms.mStatus = Status.SENT;
                cv.put(SesContract.Sms.STATUS, sms.mStatus.toInt());
                sms.mSendDate = new Date().getTime();
                cv.put(SesContract.Sms.SENDDATE, sms.mSendDate);

                new UpdateAsyncQueryHandler(context.getContentResolver()).startUpdate(789, null, uri, cv, null, null);
            }

            @Override public void onSmsSentError(final String tag, final Sms sms, final int error) {
                HelperSmsSender.displayStandardErrorText(context);
            }

        }, "sms");

    }

    /**
     * Get the conf message for a specific configuration key
     *
     * @param confKey configuration key
     * @param message sms received
     * @return the message without the confKey
     */
    public static String getConfMessageForKey(final String confKey, final String message) {
        String text = null;
        Pattern p = Pattern.compile(".*" + confKey + "=(.*)");
        Matcher m = p.matcher(message);
        if (m.matches()) {
            text = m.group(1);
        }
        return text;
    }

    public static String shortenSmsConfirmMessage(final Context ctx, String originalMessage) {
        return shortenSmsConfirmMessage(Config.getInstance(ctx), originalMessage);
    }

    public static String shortenSmsConfirmMessage(Config config, String originalMessage) {
        originalMessage = removeKeywordAndNumberFromString(originalMessage, config.getValueForKey(Config.KEYWORD_YEAR));
        originalMessage = removeKeywordAndNumberFromString(originalMessage, config.getValueForKey(Config.KEYWORD_WEEK));
        originalMessage = originalMessage.replaceAll(config.getValueForKey(Config.KEYWORD_WEEKLY) + "\\s?", "");
        originalMessage = originalMessage.replaceAll(config.getValueForKey(Config.KEYWORD_MONTHLY) + "\\s?", "");
        return originalMessage;
    }

    /**
     * Get Format message for action confirmation
     *
     * @param list
     * @return
     */
    public static String getFormatMessageForActionConfirmation(final List<Sms> list) {
        StringBuilder sb = new StringBuilder();
        for (Sms sms : list){
            sb.append(sms.toConfirmDialog(null));
            sb.append("\n");
        }
        return sb.toString();
    }

    /**
     * Get only LAST sms contained in the report
     *
     * @param listSms
     * @return List<Sms>
     */
    public static List<Sms> getOnlyLastSentSmsReport(List<Sms> listSms)
    {
        List<Sms> lastReport = new ArrayList<>();

        // filter on last report only (Greather timeStamp)
        long timeStamp = -1 ;
        for (Sms sms : listSms) {
            if (timeStamp == -1) {
                timeStamp = sms.mTimestamp ;
                lastReport.add(sms);
            } else if (timeStamp == sms.mTimestamp) {
                lastReport.add(sms);
            } else {
                break ;
            }
        }

        return lastReport ;
    }

    /***
     * Compare 2 list of Sms to check if reports are identical
     *
     * @param listSms
     * @param listSmsPreviouslySent
     */
    public static boolean compareReports(List<Sms> listSms, List<Sms> listSmsPreviouslySent){
        for (Sms sms : listSms) {
            for (Sms lastSms : listSmsPreviouslySent) {
                if (sms.mDisease.equals(lastSms.mDisease)) { // SMS with same disease
                    for(Parser.ConfigField smsField : sms.mListData) {
                        for (Parser.ConfigField lastSmsfield : lastSms.mListData) {
                            if (smsField.Name.equals(lastSmsfield.Name)) {
                                if (! smsField.Type.equals(lastSmsfield.Type)) {
                                    return false ;
                                }
                            }
                        }
                    }
                }
            }
        }

        return true ;
    }

    /**
     * Check if there is at least one or more SMS in error in this list of SMS
     *
     * @param listSms
     * @return
     */
    public static boolean containsOnErrorSms(List<Sms> listSms)
    {
        for (Sms sms : listSms) {
            if (sms.mStatus.equals(Status.ERROR)) {
                return true ;
            }
        }

        return false ;
    }


    /**
     * Return Timeout regarding the Sms type
     *
     * @param ctx
     * @param type
     * @return int
     */
    public static int getTimeoutFromTypeSms(Context ctx,  TypeSms type)
    {
        switch (type) {
            case ALERT:
                return HelperPreference.getAlertDelay(ctx);
            case WEEKLY:
                return HelperPreference.getWeekDelay(ctx);
            case MONTHLY:
                return HelperPreference.getMonthDelay(ctx);
            default:
                return -1 ;
        }
    }

    /**
     * Check if the timeout has been reached before getting an Ack confirmation
     *
     * @param ctx
     * @param sms
     *
     * @return boolean
     */
    public static boolean hasSendingTimeoutExceeded(Context ctx, Sms sms)
    {
        int delay = HelperSms.getTimeoutFromTypeSms(ctx, sms.mType);

        if (delay == -1) {
            return false ;
        }

        long now = new Date().getTime();
        long sendDate = (sms.mSendDate == 0) ? sms.mTimestamp : sms.mSendDate;  // SendDate can be == 0 as sendDate column has been added in database version 11

        return ((sendDate + (delay * 60 *1000)) < now);
    }
}
