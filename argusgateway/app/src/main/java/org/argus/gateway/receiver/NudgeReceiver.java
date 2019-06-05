
package org.argus.gateway.receiver;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;

import org.argus.gateway.App;

public class NudgeReceiver extends BroadcastReceiver {

    // As App is an extend of Application, just calling a receiver start App (So all routine).
    @Override
    public void onReceive(Context context, Intent intent) 
    {
        // intentional side-effect: initialize App class to start outgoing message poll timer,
        // and send any pending incoming messages that were persisted to DB before reboot.
        
        App app = (App)context.getApplicationContext();
        app.log("Nudged by " + intent.getAction());
        app.retryStuckMessages();
    }
}
