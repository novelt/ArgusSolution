package org.argus.sms.app.activity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;

import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;

import org.argus.sms.app.fragment.FragmentReportWeekly;
import org.argus.sms.app.utils.HelperPreference;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Activity displaying weekly report
 */
public class ActivityReportWeekly extends AbstractActivityReport {

    /**
     * Getter for the starting {@link android.content.Intent} of the Activity
     * @param ctx Application context
     * @return Staring {@link Intent} for the Activity
     */
    public static Intent getStartingIntent(Context ctx) {
        return new Intent(ctx, ActivityReportWeekly.class);
    }

    @Override
    protected void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        // Removed to get translation
        //getSupportActionBar().setTitle(Config.getInstance(this).getValueForKey(Config.KEYWORD_WEEKLY));
    }


    /**
     * Get the child fragment of the Report weekly screen
     * @return The configurated {@link FragmentReportWeekly}
     */
    @Override
    protected Fragment getChildFragment() {
        mFragment = FragmentReportWeekly.newInstance();
        return mFragment;
    }

    @Override
    public void onReportSent() {
        // nothing yo do
    }

    @Override
    protected Date getMaxDate() {
        Calendar c = new GregorianCalendar();
        int day = HelperPreference.getConfigWeekStart(getApplication());
        while (c.get(Calendar.DAY_OF_WEEK) != day)
            c.add(Calendar.DAY_OF_WEEK, -1);
        c.add(Calendar.DAY_OF_WEEK, -1);
        return c.getTime();
    }
}
