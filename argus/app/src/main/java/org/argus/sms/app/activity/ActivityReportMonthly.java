package org.argus.sms.app.activity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;

import java.util.Date;
import java.util.GregorianCalendar;

import org.argus.sms.app.fragment.FragmentReportMonthly;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Activity displaying report monthly
 */
public class ActivityReportMonthly extends AbstractActivityReport {

    /**
     * Getter for the starting {@link android.content.Intent} of the Activity
     * @param ctx Application context
     * @return Staring {@link Intent} for the Activity
     */
    public static Intent getStartingIntent(Context ctx) {
        Intent i = new Intent(ctx, ActivityReportMonthly.class);
        return i;
    }

    @Override
    protected void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        // Remove to get Translation
        //getSupportActionBar().setTitle( Config.getInstance(this).getValueForKey(Config.KEYWORD_MONTHLY));
    }

    /**
     * Get the child fragment of the Report monthly screen
     * @return The configurated {@link FragmentReportMonthly}
     */
    @Override
    protected Fragment getChildFragment() {
        mFragment = FragmentReportMonthly.newInstance();
        return mFragment;
    }

    @Override
    public void onReportSent() {
        // nothing to do
    }

    @Override
    protected Date getMaxDate() {
        return new GregorianCalendar().getTime();
    }
}
