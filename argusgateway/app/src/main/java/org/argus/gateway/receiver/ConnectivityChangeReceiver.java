
package org.argus.gateway.receiver;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;

import org.argus.gateway.App;

public class ConnectivityChangeReceiver extends BroadcastReceiver {

    @Override
    public void onReceive(Context context, Intent intent) {
        final App app = (App) context.getApplicationContext();
        
        if (!app.isEnabled())
        {
            return;
        }
        
        app.onConnectivityChanged();
    }    
}
