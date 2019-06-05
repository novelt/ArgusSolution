package org.argus.sms.app.service;

import android.app.IntentService;
import android.content.ContentValues;
import android.content.Intent;
import android.net.Uri;
import android.text.TextUtils;
import android.util.Log;

import java.util.ArrayList;

import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.ConfigApp;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperPreference;
import org.argus.sms.app.utils.HelperReminder;

/**
 * Service to handle all the reminder messages
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ServiceReminder extends IntentService {
    private final static String TAG = ServiceReminder.class.getSimpleName();
    private final static boolean DEBUG = true;

    public static ArrayList<Uri> Alarms = new ArrayList<Uri>(); // List of alarms for sent message, used when the device shutdown.

    public ServiceReminder() {
        super(ServiceReminder.class.getSimpleName());
    }

    /**
    * Called when a reminder is triggered
     */
    @Override
    protected void onHandleIntent(final Intent intent) {
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "onHandleIntent ");
        }
        if (intent != null && !TextUtils.isEmpty(intent.getAction())) {
            if (intent.getAction().equals(ConfigApp.ACTION_REMINDER)) {
                HelperReminder.displayReminderNotificationIfNeeded(getApplicationContext());
            } else if (intent.getAction().equals(ConfigApp.ACTION_NOT_CONFIRMED)) {
                TypeSms type = (TypeSms) intent.getSerializableExtra(ConfigApp.EXTRA_TYPE);
                HelperReminder.displayNotReceivedMessage(getApplicationContext(), type);
                // clear the "sending" state in the database and put a state "error"
                Uri uri = intent.getData();
                ContentValues cv = new ContentValues();
                cv.put(SesContract.Sms.STATUS, Status.ERROR.toInt());
                getContentResolver().update(uri,cv,null,null);
                // Remove the alarm from the Alarms list
                removeAlarm(uri);
            } else {
                if (BuildConfig.ERROR) {
                    Log.e(TAG, "onHandleIntent not handled");
                }
            }
        } else {
            if (BuildConfig.ERROR) {
                Log.e(TAG, "onHandleIntent intent null or action empty");
            }
        }
    }

    private void removeAlarm(Uri uri) {
        for (Uri elem : Alarms) {
            if (elem == uri) {
                Alarms.remove(elem);
                return ;
            }
        }
    }
}
