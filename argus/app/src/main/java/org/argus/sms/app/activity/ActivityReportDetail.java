package org.argus.sms.app.activity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;

import org.argus.sms.app.fragment.FragmentReportDetail;
import org.argus.sms.app.model.TypeSms;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Activity displaying report detail
 */
public class ActivityReportDetail extends AbstractActivitySesSms{

    private final static String KEY_TYPE = "key_type";
    private final static String KEY_DISEASE = "key_disease";
    private final static String KEY_LABEL = "key_label";

    /**
     * Getter for the starting {@link android.content.Intent} of the Activity
     * @param ctx application context
     * @param type type of sms to display
     * @param disease name of selected disease to display
     * @return
     */
    public static Intent getStartingIntent(Context ctx, TypeSms type, String disease, String label) {
        Intent i = new Intent(ctx,ActivityReportDetail.class);
        i.putExtra(KEY_TYPE,type);
        i.putExtra(KEY_DISEASE,disease);
        i.putExtra(KEY_LABEL,label);
        return i;
    }


    /**
     * Get the child fragment of the Report detail screen
     * @return The configurated {@link FragmentReportDetail}
     */
    @Override
    protected Fragment getChildFragment() {
        Intent i = getIntent();
        return FragmentReportDetail.newInstance((TypeSms)i.getSerializableExtra(KEY_TYPE), i.getStringExtra(KEY_DISEASE));
    }

    @Override
    protected void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        String disease = getIntent().getStringExtra(KEY_LABEL);
        getSupportActionBar().setTitle(disease);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
    }

}
