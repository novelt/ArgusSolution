package org.argus.sms.app.utils;

import android.app.AlarmManager;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.database.Cursor;
import android.graphics.BitmapFactory;
import android.net.Uri;
import android.support.v4.app.NotificationCompat;
import android.text.TextUtils;
import android.util.Log;

import java.util.Calendar;
import java.util.Date;

import hugo.weaving.DebugLog;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.ConfigApp;
import org.argus.sms.app.R;
import org.argus.sms.app.activity.ActivityDashboard;
import org.argus.sms.app.activity.ActivityPush;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.service.ServiceReminder;

/**
 * Helper class for Reminder
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class HelperReminder {
    private final static String TAG = HelperReminder.class.getSimpleName();
    private final static boolean DEBUG = true;
    public final static int NOTIFICATION_REMINDER_ID = 23;
    public final static int NOTIFICATION_MESSAGE_CONFIRM_ID = 24;

    /**
     * Check if a report is sent for the precedent week
     * @param context application context
     * @param contentResolver application contentResolver
     * @return
     */
    @DebugLog
    public static boolean isReportSentForLastWeek(Context context, ContentResolver contentResolver) {
        boolean reportSentForLastWeek = false;
        Calendar calendar = HelperCalendar.getCalendarWithCorrectStartWeek(context);
        // --------------------------------------------------------------------
        calendar.add(Calendar.WEEK_OF_YEAR, -1);
        int pastWeek = calendar.get(Calendar.WEEK_OF_YEAR);
        int year = calendar.get(Calendar.YEAR);

        String selection = SesContract.Sms.WEEK + "=? AND " + SesContract.Sms.YEAR + "=? AND " + SesContract.Sms.STATUS + "!=?";
        String[] selectionArgs = {String.valueOf(pastWeek), String.valueOf(year),String.valueOf(Status.DRAFT.toInt())};
        Cursor c = contentResolver.query(SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, null);
        if (c != null) {
            if (c.moveToFirst()) {
                reportSentForLastWeek = true;
                // remove the notification if present
                NotificationManager notificationManager =
                        (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);
                notificationManager.cancel(NOTIFICATION_REMINDER_ID);
            }
            c.close();
        }

        return reportSentForLastWeek;
    }

    /**
     * Check if a report is sent for the precedent month
     * @param context application context
     * @param contentResolver application contentResolver
     * @return
     */
    @DebugLog
    public static boolean isReportSentForLastMonth(Context context, ContentResolver contentResolver) {
        boolean reportSentForLastMonth = false;
        Calendar calendar = HelperCalendar.getCalendarWithCorrectStartWeek(context);
        // --------------------------------------------------------------------
        // month already minus one, so no need to substract
        int pastMonth = calendar.get(Calendar.MONTH);
        int year = calendar.get(Calendar.YEAR);

        String selection = SesContract.Sms.MONTH + "=? AND " + SesContract.Sms.YEAR + "=? AND " + SesContract.Sms.STATUS + "!=?";
        String[] selectionArgs = {String.valueOf(pastMonth), String.valueOf(year),String.valueOf(Status.DRAFT.toInt())};
        Cursor c = contentResolver.query(SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, null);
        if (c.moveToFirst()) {
            reportSentForLastMonth = true;
            // remove the notification if present
            NotificationManager notificationManager =
                    (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);
            notificationManager.cancel(NOTIFICATION_REMINDER_ID);
        }
        c.close();
        return reportSentForLastMonth;
    }


    /**
     * Get the message to display if the report is needed for last week
     * @param context application context
     * @param contentResolver application contentResolver
     * @return Message to display
     */
    @DebugLog
    public static String getMessageIfReportTextNeededForLastWeek(Context context, ContentResolver contentResolver) {
        String message = null;
        if (!isReportSentForLastWeek(context, contentResolver)) {
            Calendar calendar = HelperCalendar.getCalendarWithCorrectStartWeek(context);
            calendar.add(Calendar.WEEK_OF_YEAR, -1);
            int currentWeek = calendar.get(Calendar.WEEK_OF_YEAR);
            message = context.getString(R.string.reminder_send_report_for_week, currentWeek);
        }
        return message;
    }

    /**
     * Get the message to display if the report is needed for last month
     * @param context application context
     * @param contentResolver application contentResolver
     * @return Message to display
     */
    @DebugLog
    public static String getMessageIfReportTextNeededForLastMonth(Context context, ContentResolver contentResolver) {
        String message = null;
        if (!isReportSentForLastMonth(context, contentResolver)) {
            Calendar calendar = HelperCalendar.getCalendarWithCorrectStartWeek(context);
            calendar.add(Calendar.MONTH, -1);
            int currentMonth = calendar.get(Calendar.MONTH);
            message = context.getString(R.string.reminder_send_report_for_month, currentMonth+1);
        }
        return message;
    }

    /**
     * Setup the Repeat AlarmManager for the week reminder
     * @param ctx application context
     */
    public static void setUpRepeatAlarmCheck(Context ctx) {
        Calendar calendar = HelperCalendar.getCalendarWithCorrectStartWeek(ctx);
        calendar.set(Calendar.HOUR_OF_DAY, 10);
        calendar.set(Calendar.MINUTE, 0);
        calendar.set(Calendar.SECOND, 0);
        Intent i = new Intent(ctx, ServiceReminder.class);
        i.setAction(ConfigApp.ACTION_REMINDER);
        PendingIntent pi = PendingIntent.getService(ctx, 0, i, PendingIntent.FLAG_UPDATE_CURRENT);
        AlarmManager am = (AlarmManager) ctx.getSystemService(Context.ALARM_SERVICE);
        long interval = AlarmManager.INTERVAL_DAY;
        am.setInexactRepeating(AlarmManager.RTC_WAKEUP, calendar.getTimeInMillis(), interval, pi);
    }


    /**
     * Display the reminder as a notification if needed
     * @param context application context
     */
    public static void displayReminderNotificationIfNeeded(final Context context) {
        String message = HelperReminder.getMessageIfReportTextNeededForLastWeek(context, context.getContentResolver());
        if(TextUtils.isEmpty(message)) {
            // If weekly report enabled
            if (!TextUtils.isEmpty(Config.getInstance(context).getValueForKey(Config.KEYWORD_WEEK)))
                displayReminderNotificationIfNeeded(context, message, NOTIFICATION_REMINDER_ID, PendingIntent.FLAG_UPDATE_CURRENT, false);
        }else {
            // If monthly report enabled
             if (!TextUtils.isEmpty(Config.getInstance(context).getValueForKey(Config.KEYWORD_MONTH))) {
                String messageMonth = HelperReminder.getMessageIfReportTextNeededForLastMonth(context, context.getContentResolver());
                if (TextUtils.isEmpty(messageMonth)) {
                    displayReminderNotificationIfNeeded(context, messageMonth, NOTIFICATION_REMINDER_ID, PendingIntent.FLAG_UPDATE_CURRENT, false);
                }
             }
        }
    }

    /**
     * Setup the reminder for SMS not confirmed
     *
     * @param ctx application context
     * @param type Type of sms
     * @param uri Uri to the data to confirm
     */
    @DebugLog
    public static void setUpReminderNotConfirmed(Context ctx, TypeSms type, Uri uri)
    {
        long timeout = HelperSms.getTimeoutFromTypeSms(ctx, type);

        if (timeout != -1) {
            timeout = timeout * 60 * 1000;
            Intent i = new Intent(ctx, ServiceReminder.class);
            i.setAction(ConfigApp.ACTION_NOT_CONFIRMED);
            i.setData(uri);
            i.putExtra(ConfigApp.EXTRA_TYPE, type);
            PendingIntent pi = PendingIntent.getService(ctx, 0, i, PendingIntent.FLAG_UPDATE_CURRENT);
            AlarmManager am = (AlarmManager) ctx.getSystemService(Context.ALARM_SERVICE);
            am.set(AlarmManager.RTC_WAKEUP, new Date().getTime() + timeout, pi);
            ServiceReminder.Alarms.add(uri);
        } else {
            if (BuildConfig.DEBUG && DEBUG) {
                Log.d(TAG, "setUpReminderNotConfirmed timeoutNotSetted");
            }
        }
    }

    /**
     * Remove the reminder in the AlarmManager to avoid the tick
     * @param ctx application context
     * @param uri Uri to the data to clear
     */
    public static void clearReminderNotConfirmed(Context ctx, Uri uri) {
        Intent i = new Intent(ctx, ServiceReminder.class);
        i.setAction(ConfigApp.ACTION_NOT_CONFIRMED);
        i.setData(uri);
        PendingIntent pi = PendingIntent.getService(ctx, 0, i, PendingIntent.FLAG_UPDATE_CURRENT);
        AlarmManager am = (AlarmManager) ctx.getSystemService(Context.ALARM_SERVICE);
        am.cancel(pi);
    }

    /**
     * Display the reminder for a not confirmed message
     * @param context application context
     * @param type Type of sms
     */
    public static void displayNotReceivedMessage(final Context context, final TypeSms type) {
        String message = null;
        switch (type) {
            case ALERT:
                message = HelperPreference.getAlertMessage(context);
                break;
            case WEEKLY:
                message = HelperPreference.getWeekMessage(context);
                break;
            case MONTHLY:
                message = HelperPreference.getMonthMessage(context);
                break;
        }
        HelperPreference.saveLastMessage(context, message);
        //displayReminderNotificationIfNeeded(context, message, NOTIFICATION_MESSAGE_CONFIRM_ID, 0, true);
    }

    /**
     * Display the notification if message not empty
     * @param context application context
     * @param message message to display
     * @param notifId notification identifier
     * @param flag flag for notification
     * @param cancelable is the notification cancelable
     */
    public static void displayReminderNotificationIfNeeded(final Context context, final String message, int notifId, int flag, boolean cancelable) {
        if (!TextUtils.isEmpty(message)) {
            NotificationCompat.Builder notificationBuilder = new NotificationCompat.Builder(context);
            String appName = context.getString(R.string.application_name);
            notificationBuilder.setContentTitle(appName);
            notificationBuilder.setContentText(message);
            notificationBuilder.setSmallIcon(R.drawable.ic_alerte);
            notificationBuilder.setLargeIcon(BitmapFactory.decodeResource(context.getResources(), R.drawable.argus_logo));
            Intent i = new Intent(context, ActivityDashboard.class);
            i.putExtra(ActivityPush.EXTRA_PUSH_TEXT,message);
            PendingIntent pi = PendingIntent.getActivity(context, notifId, i, flag);
            notificationBuilder.setContentIntent(pi);
            notificationBuilder.setAutoCancel(cancelable);
            if (cancelable) {
                notificationBuilder.setTicker(appName);
            } else {
                // leave it displayed until report not sent
                notificationBuilder.setOngoing(true);
                notificationBuilder.setOnlyAlertOnce(true);
            }
            notificationBuilder.setStyle(new NotificationCompat
                    .BigTextStyle()
                    .bigText(message));
            // ----------------------------------------
            Notification notif = notificationBuilder.build();
            NotificationManager notificationManager =
                    (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);
            notificationManager.notify(notifId, notif);
        }
    }
}
