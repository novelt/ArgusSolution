package org.argus.sms.app.receiver;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.telephony.SmsMessage;
import android.util.Log;

import java.util.Date;
import java.util.HashMap;
import java.util.Map;

import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.ConfigApp;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.EventSyncFinished;
import org.argus.sms.app.utils.EventSyncMessageReceivedCount;
import org.argus.sms.app.utils.HelperPreference;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.OttoSingleton;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class SmsReceiver extends BroadcastReceiver {
    private final static String     TAG = SmsReceiver.class.getSimpleName();
    private final static boolean    DEBUG = true;
    private final static boolean    VERBOSE = true;
    private static final int        REQUEST_CODE_DASHBOARD = 12;

    @Override
    public void onReceive(final Context context, final Intent intent) {
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "onReceive ");
        }

        try {
            processReceivedMessage(context, intent);
        } catch (Exception e) {
            Log.e("SmsReceiver", "Exception smsReceiver" + e);
            e.printStackTrace();
        }
    }

    /**
     * TODO : Parse sms if Is Config updated by SMS before everything else
     *
     * @param context
     * @param intent
     */
    private void processReceivedMessage(final Context context, final Intent intent) {
        Map<String, String> messages = retreiveMessages(intent);
        for (String phoneNumber : messages.keySet()) {
            String message = messages.get(phoneNumber);
            boolean isConfig = HelperSms.parseSmsIfConfig(context, message);
            if (isConfig) {
                HelperSms.saveSmsToDatabase(context, message);
                int count = HelperPreference.getlastSyncCount(context);
                count++;
                HelperPreference.saveLastSyncCount(context, count);
                OttoSingleton.getInstance().getBus().post(new EventSyncMessageReceivedCount(count));
                if (count == HelperPreference.getConfigSmsCount(context)) { // I see no reason for that yet... || count + 1 == HelperPreference.getConfigSmsCount(context)) {
                    HelperPreference.saveLastSyncCount(context, 0);
                    if (BuildConfig.DEBUG && DEBUG) {
                        Log.d(TAG, "onReceive sync complete");
                    }
                    // delete old config
                    String selection = HelperSms.getSmsConfigAndModelCountSelection(false);
                    String[] selectionArgs = HelperSms.getSmsConfigAndModelCountSelectionArgs(context);
                    context.getContentResolver().delete(SesContract.Sms.CONTENT_URI, selection, selectionArgs);
                    HelperPreference.saveLastSync(context, new Date().getTime());
                    HelperPreference.saveLastSyncId(context, HelperPreference.getWaitingSyncId(context));
                    HelperPreference.clearCurrentSyncData(context);
                    OttoSingleton.getInstance().getBus().post(new EventSyncFinished());
                    // Delete drafts reports to make them reload with this new configuration
                    deleteDraftReports(context);
                }
                this.abortBroadcast();
            } else if (HelperPreference.isPhoneNumberAValidServer(context, phoneNumber)) {
                if (HelperSms.isMessageContainsSyncRequest(message)) {
                    if (BuildConfig.DEBUG && DEBUG) {
                        Log.d(TAG, "onReceive sync request");
                    }
                    // no listener needed
                    HelperSms.sendSyncRequest(context.getApplicationContext(),null);
                } else {
                    TypeSms typeSms = HelperSms.saveSmsToDatabase(context, message);
                    if (BuildConfig.DEBUG && DEBUG) {
                        Log.d(TAG, "onReceive  message=" + message);
                    }

                    if ((typeSms == TypeSms.ERROR || typeSms == TypeSms.OTHER || typeSms == TypeSms.THRESHOLD) && !message.toLowerCase().contains("template")) {
                        saveLastMessageAndBuildAndShowNotification(context, message);
                    }
                }
                this.abortBroadcast();
            } else {
                System.out.println("processReceivedMessage : Other case");
            }
        }
    }

    private void deleteDraftReports(final Context context) {
        String selection = SesContract.Sms.STATUS + "=?";
        String[] selectionArgs = {String.valueOf(Status.DRAFT.toInt())};
        int count = context.getContentResolver().delete(SesContract.Sms.CONTENT_URI, selection, selectionArgs);
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "deleteDraftReports count=" + count);
        }
    }

    private void saveLastMessageAndBuildAndShowNotification(final Context context, String message) {
        message = HelperSms.removeAndroidIdFromString(message);
        message = HelperSms.removeWordFromString(message, ConfigApp.SMS_THRESHOLD);
        message = message.replace(ConfigApp.SMS_OK, "");
        HelperPreference.saveLastMessage(context, message);

        /******** Delete Notification Popup ******************/
       // String appName = context.getString(org.argus.sms.app.R.string.application_name);

       // NotificationCompat.Builder notificationBuilder = new NotificationCompat.Builder(context);
       // notificationBuilder.setContentTitle(appName);
       // notificationBuilder.setContentText(message);
       // notificationBuilder.setTicker(appName);
       // notificationBuilder.setSmallIcon(org.argus.sms.app.R.drawable.ic_stat_icon_notif);
       // notificationBuilder.setLargeIcon(BitmapFactory.decodeResource(context.getResources(), org.argus.sms.app.R.drawable.ic_launcher));

       // Intent i = new Intent(context, ActivityDashboard.class);
       // i.putExtra(ActivityPush.EXTRA_PUSH_TEXT,message);
       // PendingIntent pi = PendingIntent.getActivity(context, REQUEST_CODE_DASHBOARD, i, PendingIntent.FLAG_UPDATE_CURRENT);

       // notificationBuilder.setContentIntent(pi);
       // notificationBuilder.setAutoCancel(true);
       // notificationBuilder.setStyle(new NotificationCompat
       //         .BigTextStyle()
       //         .bigText(message));

       // Notification notif = notificationBuilder.build();
       // NotificationManager mNotificationManager =
       //         (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);

        // mId allows you to update the notification later on.
       // mNotificationManager.notify(REQUEST_CODE_DASHBOARD, notif);

        HelperPreference.saveTextToDisplayInPushScreen(context, message);
    }

    private static Map<String, String> retreiveMessages(Intent intent) {
        Map<String, String> msg = null;
        SmsMessage[] msgs;
        Bundle bundle = intent.getExtras();

        if (bundle != null && bundle.containsKey("pdus")) {
            Object[] pdus = (Object[]) bundle.get("pdus");

            if (pdus != null) {
                int nbrOfpdus = pdus.length;
                msg = new HashMap<String, String>(nbrOfpdus);
                msgs = new SmsMessage[nbrOfpdus];

                // There can be multiple SMS from multiple senders, there can be a maximum of nbrOfpdus different senders
                // However, send long SMS of same sender in one message
                for (int i = 0; i < nbrOfpdus; i++) {
                    msgs[i] = SmsMessage.createFromPdu((byte[]) pdus[i]);

                    String originatinAddress = msgs[i].getDisplayOriginatingAddress();

                    // Check if index with number exists
                    if (!msg.containsKey(originatinAddress)) {
                        // Index with number doesn't exist
                        // Save string into associative array with sender number as index
                        msg.put(msgs[i].getOriginatingAddress(), msgs[i].getDisplayMessageBody());

                    } else {
                        // Number has been there, add content but consider that
                        // msg.get(originatinAddress) already contains sms:sndrNbr:previousparts of SMS,
                        // so just add the part of the current PDU
                        String previousparts = msg.get(originatinAddress);
                        String msgString = previousparts + msgs[i].getMessageBody();
                        msg.put(originatinAddress, msgString);
                    }
                }
            }
        }

        return msg;
    }
}
