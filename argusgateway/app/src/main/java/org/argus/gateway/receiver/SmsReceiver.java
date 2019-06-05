package org.argus.gateway.receiver;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.telephony.SmsMessage;

import org.argus.gateway.App;
import org.argus.gateway.IncomingMessage;
import org.argus.gateway.IncomingSms;

import java.util.ArrayList;
import java.util.List;

public class SmsReceiver extends BroadcastReceiver {

    private static final String SMS_RECEIVED = "android.provider.Telephony.SMS_RECEIVED";

    private App app;
    // If is a test avoid abortBroadcast
    private boolean mIsTest = false;

    public SmsReceiver(boolean isTest) {
        mIsTest = isTest;
    }

    public SmsReceiver() {}

    @Override
    public void onReceive(Context context, Intent intent) {

        app = (App) context.getApplicationContext();

        if (app == null || !app.isEnabled())
        {
            app.log("Ignoring incoming SMS as application is disable");
            return;
        }

        try {
            IncomingMessage sms = getMessageFromIntent(intent);

            if (sms.isForwardable())
            {
                // With android 4.4 and upper ArgusGateway must be the default messaging application
                // The SMS_RECEIVED must be ignored after 4.4
                String action = intent.getAction();
                if (action.contains(SMS_RECEIVED) && Build.VERSION.SDK_INT >= 19)
                    return;

                app.inbox.forwardMessage(sms);

                if (!app.getKeepInInbox() && !mIsTest)
                {
                    this.abortBroadcast();
                }
            }
            else
            {
                app.log("Ignoring incoming SMS from " + sms.getFrom());
            }
        } catch (Throwable ex) {
            app.logError("Unexpected error in SmsReceiver", ex, true);
        }
    }

    private IncomingMessage getMessageFromIntent(Intent intent) 
    {
        Bundle bundle = intent.getExtras();        

        // SMSDispatcher may send us multiple pdus from a multipart sms,
        // in order (all in one broadcast though)
        
        // The comments in the gtalksms app indicate that we could get PDUs 
        // from multiple different senders at once, but I don't see how this
        // could happen by looking at the SMSDispatcher source code... 
        // so I'm going to assume it doesn't happen and throw an exception if
        // it does.        
        
        List<SmsMessage> smsParts = new ArrayList<SmsMessage>();
        
        for (Object pdu : (Object[]) bundle.get("pdus"))
        {
            smsParts.add(SmsMessage.createFromPdu((byte[]) pdu));
        }
                
        return new IncomingSms(app, smsParts);
    }
}