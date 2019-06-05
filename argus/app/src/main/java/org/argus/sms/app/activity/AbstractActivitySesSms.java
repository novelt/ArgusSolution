package org.argus.sms.app.activity;

import android.database.Cursor;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v7.app.ActionBarActivity;

import com.crashlytics.android.Crashlytics;

import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.provider.SesProvider;
import org.argus.sms.app.utils.HelperPreference;

import io.fabric.sdk.android.Fabric;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Abstract class used by all screens in application to get the single displayed fragment
 */
public abstract class AbstractActivitySesSms extends ActionBarActivity {

    private int mReportId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Set default Language
        String lang = HelperPreference.getLanguage(this);
        if (lang != null && !lang.isEmpty())
            HelperPreference.ChangeLanguage(this, lang);

        Fabric.with(this, new Crashlytics());
        setContentView(org.argus.sms.app.R.layout.single_pane_container);
        if (savedInstanceState == null) {
            getSupportFragmentManager().beginTransaction()
                    .add(org.argus.sms.app.R.id.container, getChildFragment())
                    .commit();
        }else{
            Fragment f = getSupportFragmentManager().findFragmentById(org.argus.sms.app.R.id.container);
            if (f != null){
                saveFragmentFromRestoredState(f);
            }
        }

        fillReportId();
    }

    /**
     * Fill Report Id for all SMS in this report
     */
    protected void fillReportId()
    {
        Cursor cursor = this.getApplicationContext().getContentResolver().query(SesContract.Sms.buildLastReportIdUri(), null, null, null, null);

        if (cursor != null) {
            cursor.moveToFirst();
            mReportId = cursor.getInt(0);
            cursor.close();
        }

        mReportId ++ ;
    }

    protected void saveFragmentFromRestoredState(final Fragment f){

    }

    /**
     * Get the fragment to display in screen
     * @return
     */
    protected abstract Fragment getChildFragment();

    /**
     * Get the ReportId
     *
     * @return
     */
    public int getReportId()
    {
        return this.mReportId ;
    }
}
