package org.argus.sms.app.receiver;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;

import org.argus.sms.app.utils.HelperReminder;

/**
 * Receiver called at the boot of the phone
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class BootReceiver extends BroadcastReceiver{

    /**
     * Setup the repeat alarm at boot
     * @param context
     * @param intent
     */
    @Override
    public void onReceive(final Context context, final Intent intent) {
        // If the boot is complete.
        if (intent.getAction().equals(Intent.ACTION_BOOT_COMPLETED)) {
            HelperReminder.setUpRepeatAlarmCheck(context);
        }
    }
}
