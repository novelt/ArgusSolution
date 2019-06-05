package org.argus.gateway;

/**
 * Created by alexandre on 14/01/16.
 */
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.widget.Toast;

import java.util.ArrayList;

public class QuerySlavesReceiver extends BroadcastReceiver
{
    public void onReceive(Context paramContext, Intent paramIntent)
    {
        Toast.makeText(paramContext, "QuerySlavesReceiver by " + paramContext.getPackageName(), Toast.LENGTH_LONG).show();

        ArrayList<String> list = getResultExtras(false).getStringArrayList(App.QUERY_SLAVES_EXTRA_PACKAGES) ;
        if (list != null) {
            list.add(paramContext.getPackageName());
        }
    }
}
