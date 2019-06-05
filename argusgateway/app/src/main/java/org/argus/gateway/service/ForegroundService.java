/*
 * Copyright (C) 2009 The Android Open Source Project
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package org.argus.gateway.service;

import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.os.IBinder;
import android.os.PowerManager;
import android.util.Log;
import android.support.v4.app.NotificationCompat;

import org.argus.gateway.App;
import org.argus.gateway.R;

import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import org.argus.gateway.ui.Main;

/*
 *  Service running in foreground to make sure App instance stays 
 *  in memory (to avoid losing pending messages and timestamps of 
 *  sent messages).
 * 
 *  Also adds notification to status bar. 
 */
public class ForegroundService extends Service {
    public static final String TAG = ForegroundService.class.getName();

    private App app;
    //PowerManager.WakeLock mWaveLock ;
    
    private static final Class<?>[] mSetForegroundSignature = new Class[] {
        boolean.class};
    private static final Class<?>[] mStartForegroundSignature = new Class[] {
        int.class, Notification.class};
    private static final Class<?>[] mStopForegroundSignature = new Class[] {
        boolean.class};

    private NotificationManager mNM;
    private Method mSetForeground;
    private Method mStartForeground;
    private Method mStopForeground;
    private Object[] mSetForegroundArgs = new Object[1];
    private Object[] mStartForegroundArgs = new Object[2];
    private Object[] mStopForegroundArgs = new Object[1];

    void invokeMethod(Method method, Object[] args) {
        try {
            method.invoke(this, args);
        } catch (InvocationTargetException e) {
            // Should not happen.
            Log.w("ApiDemos", "Unable to invoke method", e);
        } catch (IllegalAccessException e) {
            // Should not happen.
            Log.w("ApiDemos", "Unable to invoke method", e);
        }
    }

    /**
     * This is a wrapper around the new startForeground method, using the older
     * APIs if it is not available.
     */
    void startForegroundCompat(int id, Notification notification) {
        // If we have the new startForeground API, then use it.
        if (mStartForeground != null) {
            mStartForegroundArgs[0] = Integer.valueOf(id);
            mStartForegroundArgs[1] = notification;
            invokeMethod(mStartForeground, mStartForegroundArgs);
            return;
        }
        // Fall back on the old API.
        mSetForegroundArgs[0] = Boolean.TRUE;
        invokeMethod(mSetForeground, mSetForegroundArgs);
        mNM.notify(id, notification);
    }

    /**
     * This is a wrapper around the new stopForeground method, using the older
     * APIs if it is not available.
     */
    void stopForegroundCompat(int id) {
        // If we have the new stopForeground API, then use it.
        if (mStopForeground != null) {
            mStopForegroundArgs[0] = Boolean.TRUE;
            invokeMethod(mStopForeground, mStopForegroundArgs);
            return;
        }

        // Fall back on the old API.  Note to cancel BEFORE changing the
        // foreground state, since we could be killed at that point.
        mNM.cancel(id);
        mSetForegroundArgs[0] = Boolean.FALSE;
        invokeMethod(mSetForeground, mSetForegroundArgs);
    }

    @Override
    public void onCreate() {

        // Acquire WakeLock to be sure running Runnable even if phone is in power save mode (screen off)
       // PowerManager pm = (PowerManager) getSystemService(Context.POWER_SERVICE);
       // mWaveLock = pm.newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, TAG);
       // mWaveLock.acquire();

        mNM = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
        app = (App)getApplication();
        try {
            mStartForeground = getClass().getMethod("startForeground",
                    mStartForegroundSignature);
            mStopForeground = getClass().getMethod("stopForeground",
                    mStopForegroundSignature);
            return;
        } catch (NoSuchMethodException e) {
            // Running on an older platform.
            mStartForeground = mStopForeground = null;
        }
        try {
            mSetForeground = getClass().getMethod("setForeground",
                    mSetForegroundSignature);
        } catch (NoSuchMethodException e) {
            throw new IllegalStateException(
                    "OS doesn't have Service.startForeground OR Service.setForeground!");
        }
    }

    @Override
    public void onDestroy() {
        // Make sure our notification is gone.
        stopForegroundCompat(R.string.service_started);

        //mWaveLock.release();
    }

    //@Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        handleCommand(intent);
        // We want this service to continue running until it is explicitly
        // stopped, so return sticky.
        return Service.START_STICKY ; //1;
    }

    void handleCommand(Intent intent) 
    {        
        if (app.isEnabled())           
        {
            CharSequence text = getText(R.string.service_started);
            CharSequence info = getText(R.string.running);

           /* Notification notification = new Notification(R.drawable.icon, text,
                    System.currentTimeMillis());*/

            PendingIntent contentIntent = PendingIntent.getActivity(this, 0,
                    new Intent(this, Main.class), 0);

            Notification notification = new NotificationCompat.Builder(this)
                    .setSmallIcon(R.drawable.icon)
                    .setContentTitle(text)
                    .setContentText(info)
                    .setContentIntent(contentIntent)
                    .build();

            //notification.setLatestEventInfo(this, info, text, contentIntent);

            startForegroundCompat(R.string.service_started, notification);            
        }
        else
        {
            this.stopForegroundCompat(R.string.service_started);
        }
    }

    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }
}