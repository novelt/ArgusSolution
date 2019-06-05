package org.argus.sms.app.activity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;

import org.argus.sms.app.fragment.FragmentHistory;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.utils.HelperHistory;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Activity displaying history
 */
public class ActivityHistory extends AbstractActivitySesSms implements FragmentHistory.IFragmentHistoryListener
{
    /**
     * Getter for the starting {@link android.content.Intent} of the Activity
     * @param ctx Application context
     * @return starting intent of the activity
     * @see android.content.Intent
     */
    public static Intent getStartingIntent(Context ctx)
    {
        Intent i = new Intent(ctx,ActivityHistory.class);
        return i;
    }

    @Override
    protected void onCreate(final Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        // Check if all SMS in sent status have been updated after a reboot or an issue with the alarm manager
        HelperHistory.checkHistoryStatus(getApplicationContext());

    }

    @Override
    protected Fragment getChildFragment()
    {
        return FragmentHistory.newInstance();
    }

    @Override
    public void onItemSelected(TypeSms type,String week, String month, String year, String timestamp)
    {
        Intent i = ActivityHistoryDetail.getStartingIntent(this,type,week,month,year,timestamp);
        startActivity(i);
    }
}
