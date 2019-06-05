package org.argus.sms.app.activity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;

import org.argus.sms.app.R;
import org.argus.sms.app.fragment.FragmentHistoryDetail;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.TypeSms;


/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Activity displaying history detail
 */
public class ActivityHistoryDetail extends AbstractActivitySesSms {

    private static final String KEY_TYPE = "KEY_TYPE";
    private static final String KEY_WEEK = "KEY_WEEK";
    private static final String KEY_MONTH = "KEY_MONTH";
    private static final String KEY_YEAR = "KEY_YEAR";
    private static final String KEY_TIMESTAMP = "KEY_TIMESTAMP";


    /**
     *
     * Getter for the starting {@link android.content.Intent} of the Activity
     * @param ctx Application context
     * @param type type of sms to display
     * @param week week of sms to display
     * @param month month of sms to display
     * @param year year of sms to display
     * @param timestamp timestamp of sms to display
     * @return starting intent of the activity
     * @see android.content.Intent
     */
    public static Intent getStartingIntent(Context ctx, TypeSms type, String week, String month, String year, String timestamp) {
        Intent i = new Intent(ctx, ActivityHistoryDetail.class);
        i.putExtra(KEY_TYPE,type.toInt());
        i.putExtra(KEY_WEEK,week);
        i.putExtra(KEY_MONTH,month);
        i.putExtra(KEY_YEAR,year);
        i.putExtra(KEY_TIMESTAMP,timestamp);
        return i;
    }

    @Override
    protected void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        TypeSms sms = TypeSms.fromInt(getIntent().getIntExtra(KEY_TYPE,0));
        String keyword = null;
        switch (sms){
            case WEEKLY :
                //keyword = Config.KEYWORD_WEEKLY;
                keyword = getString(R.string.report_weekly);
                break;
            case MONTHLY :
                //keyword = Config.KEYWORD_MONTHLY;
                keyword = getString(R.string.report_monthly);
                break;
            case ALERT:
                //keyword = Config.KEYWORD_ALERT;
                keyword = getString(R.string.alert);
                break;
        }
        getSupportActionBar().setTitle(keyword);
    }

    /**
     * Get the child fragment of the History detail screen
     * @return The configurated {@link FragmentHistoryDetail}
     */
    @Override
    protected Fragment getChildFragment() {
        TypeSms sms = TypeSms.fromInt(getIntent().getIntExtra(KEY_TYPE,0));
        String week = getIntent().getStringExtra(KEY_WEEK);
        String month = getIntent().getStringExtra(KEY_MONTH);
        String year = getIntent().getStringExtra(KEY_YEAR);
        String timestamp = getIntent().getStringExtra(KEY_TIMESTAMP);
        return FragmentHistoryDetail.newInstance(sms,week,month,year,timestamp);
    }
}
