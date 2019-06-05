
package org.argus.gateway.receiver;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;

import org.argus.gateway.App;
import org.argus.gateway.IncomingMessage;

public class IncomingMessageRetry extends BroadcastReceiver
{
    @Override
    public void onReceive(Context context, Intent intent) 
    {        
        App app = (App) context.getApplicationContext();
        if (!app.isEnabled())
        {
            return;
        }
        
        IncomingMessage message = app.inbox.getMessage(intent.getData());
        
        if (message == null)
        {
            return;
        }
        
        app.inbox.enqueueMessage(message);        
    }        
}    
