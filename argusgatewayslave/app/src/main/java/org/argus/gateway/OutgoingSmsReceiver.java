package org.argus.gateway;

import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.telephony.SmsManager;
import android.widget.Toast;

import java.util.ArrayList;

/**
 * Broadcast receiver that get sms to send from mother app and send it
 */
public class OutgoingSmsReceiver extends BroadcastReceiver
{
    public void onReceive(Context paramContext, Intent paramIntent)
    {
        Toast.makeText(paramContext, "OutgoingSmsReceiver by " + paramContext.getPackageName(), Toast.LENGTH_SHORT).show();

        Object localObject = paramIntent.getExtras();
        Bundle bundle = ((Bundle)localObject) ;
        if (bundle == null) {
            return ;
        }

        String str = bundle.getString(App.OUTGOING_SMS_EXTRA_TO);
        ArrayList<String> localArrayList1 = bundle.getStringArrayList(App.OUTGOING_SMS_EXTRA_BODY);
        boolean bool = bundle.getBoolean(App.OUTGOING_SMS_EXTRA_DELIVERY_REPORT, false);
        SmsManager localSmsManager = SmsManager.getDefault();
        ArrayList<PendingIntent> localArrayList2 = new ArrayList<>();

        ArrayList<PendingIntent> deliveryIntents = null;
        if (bool) {
            deliveryIntents = new ArrayList<>();
        }
        int j = (localArrayList1 != null ? localArrayList1.size() : 0);
        int i = 0;
        while (i < j)
        {
            // Create message sent callback to the mother application
            Intent localIntent = new Intent(App.MESSAGE_STATUS_INTENT, paramIntent.getData());
            localIntent.putExtra(App.STATUS_EXTRA_INDEX, i);
            localIntent.putExtra(App.STATUS_EXTRA_NUM_PARTS, j);
            localArrayList2.add(PendingIntent.getBroadcast(paramContext, 0, localIntent, PendingIntent.FLAG_ONE_SHOT));
            if (bool)
            {
                // Create message delivered callback to the mother application
                localIntent = new Intent(App.MESSAGE_DELIVERY_INTENT, paramIntent.getData());
                localIntent.putExtra(App.STATUS_EXTRA_INDEX, i);
                localIntent.putExtra(App.STATUS_EXTRA_NUM_PARTS, j);
                deliveryIntents.add(PendingIntent.getBroadcast(paramContext, 0, localIntent, PendingIntent.FLAG_ONE_SHOT));
            }

            i += 1;
        }

        localSmsManager.sendMultipartTextMessage(str, null, localArrayList1, localArrayList2, deliveryIntents);
    }
}