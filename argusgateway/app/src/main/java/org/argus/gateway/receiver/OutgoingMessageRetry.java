
package org.argus.gateway.receiver;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;

import org.argus.gateway.App;
import org.argus.gateway.SmsSendManager.OutgoingMessage;

public class OutgoingMessageRetry extends BroadcastReceiver
{
    @Override
    public void onReceive(Context context, Intent intent) 
    {        
        App app = (App) context.getApplicationContext();
        if (!app.isEnabled())
        {
            return;
        }        
        
        OutgoingMessage message = app.outbox.getMessage(intent.getData());
        if (message == null)
        {
            return;
        }
        
        app.outbox.enqueueMessage(message);
    }
}    
