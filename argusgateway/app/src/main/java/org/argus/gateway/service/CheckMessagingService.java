
package org.argus.gateway.service;

import android.app.IntentService;
import android.content.Intent;

import org.argus.gateway.App;
import org.argus.gateway.IncomingMms;
import org.argus.gateway.IncomingSms;
import org.argus.gateway.MessagingUtils;

import java.util.List;

public class CheckMessagingService extends IntentService
{
    private App app;
    private MessagingUtils messagingUtils;

    public CheckMessagingService(String name)
    {
        super(name);        
    }
    
    public CheckMessagingService()
    {
        this("CheckMessagingService");
    }

    @Override
    public void onCreate() {
        super.onCreate();

        app = (App)this.getApplicationContext();
        
        messagingUtils = app.getMessagingUtils();
    }        

    @Override
    protected void onHandleIntent(Intent intent)
    {            
        checkNewSentSms();
        
        checkNewMms();
    }
    
    private void checkNewSentSms()
    {
        List<IncomingSms> messages = messagingUtils.getSentSmsMessages(true);
        for (IncomingSms sms : messages)
        {
            messagingUtils.markSeenSentSms(sms);
            
            if (sms.isForwardable())
            {
                app.log("SMS id=" + sms.getMessagingId() + " sent via Messaging app");
                app.inbox.forwardMessage(sms);
            }
            else
            {
                app.getMessagingUtils().deleteFromSmsInbox(sms);
                //app.log("Ignoring SMS sent via Messaging app");
            }
        }
    }
    
    private void checkNewMms()
    {
        List<IncomingMms> messages = messagingUtils.getMessagesInMmsInbox(true);
        for (IncomingMms mms : messages)
        {       
            String from = mms.getFrom();
            if (from == null || from.length() == 0)
            {
                // sender phone number may not be written to Messaging database yet
                continue;
            }
            
            // prevent forwarding MMS messages that existed in inbox
            // before ArgusGateway started, or re-forwarding MMS multiple
            // times if we don't delete them.
            messagingUtils.markSeenMms(mms);                 

            if (mms.isForwardable())
            {
                app.log("New MMS id=" + mms.getMessagingId() + " in inbox");
                app.inbox.forwardMessage(mms);                                    
            }
            else
            {
                app.log("Ignoring incoming MMS from " + mms.getFrom());
            }
        }        
    }
}