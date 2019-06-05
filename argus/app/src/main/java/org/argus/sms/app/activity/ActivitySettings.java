package org.argus.sms.app.activity;

import android.content.Intent;
import android.content.pm.ActivityInfo;
import android.content.pm.PackageManager;
import android.content.res.Configuration;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.NavUtils;
import android.view.MenuItem;

import org.argus.sms.app.fragment.FragmentSettings;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Activity displaying settings
 */
public class ActivitySettings extends AbstractActivitySesSms {

    @Override
    protected void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        // this part is used to update the activity label.
        PackageManager pm = getPackageManager();
        try {
            ActivityInfo ai = pm.getActivityInfo(this.getComponentName(), PackageManager.GET_ACTIVITIES|PackageManager.GET_META_DATA);
            if (ai.labelRes != 0) {
                getSupportActionBar().setTitle(ai.labelRes);
            }
        } catch (PackageManager.NameNotFoundException e) {
            e.printStackTrace();
        }
    }

    /**
     * Get the child fragment of the Settings screen
     * @return The {@link FragmentSettings}
     */
    @Override
    protected Fragment getChildFragment() {
        return new FragmentSettings();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();
        if (id == android.R.id.home) {
            NavUtils.navigateUpFromSameTask(this);
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    /**
     * override this class to refresh the activity when languages change.
     * To work this function need that the activity in the manifest get as parameter :
     * android:configChanges="locale"
     * @param newConfig the new configuration
     */
    @Override
    public void onConfigurationChanged(Configuration newConfig) {

        getResources().updateConfiguration(newConfig, getResources().getDisplayMetrics());

        Intent dash = new Intent(this, ActivityDashboard.class);
        dash.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        Intent intent = getIntent();
        finish();
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);

        startActivity(dash);
        startActivity(intent);

        super.onConfigurationChanged(newConfig);
    }
}
