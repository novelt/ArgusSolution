package org.argus.gateway.receiver;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.os.PowerManager;

import org.argus.gateway.App;

public class DeviceStatusPoller extends BroadcastReceiver
{
    @Override
    public void onReceive(Context context, Intent intent)
    {
        App app = (App) context.getApplicationContext();

        if (!app.isEnabled()) {
            return;
        }

        PowerManager pm = (PowerManager)context.getSystemService(Context.POWER_SERVICE);
        boolean isScreenOn = pm.isScreenOn();

        if (!isScreenOn)
        {
            app.log("screen is OFF");
            try {
                PowerManager.WakeLock wl_cpu = pm.newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "MyCpuLock");
                wl_cpu.acquire();
            } catch (Exception ex) {
                ex.printStackTrace();
            }

        } else {
            app.log("screen is ON");
        }

        app.checkDeviceStatus();
    }
}
