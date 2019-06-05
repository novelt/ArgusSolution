package org.argus.sms.app.activity;

import android.os.Bundle;
import android.support.v4.app.Fragment;

import org.argus.sms.app.fragment.FragmentSynchronization;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Activity displaying the synchronization
 */
public class ActivitySynchronization extends AbstractActivitySesSms implements FragmentSynchronization.IFragmentSynchronizationListener {


    @Override
    protected void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
    }

    /**
     * Get the child fragment of the Settings screen
     * @return The {@link FragmentSynchronization}
     */
    @Override
    protected Fragment getChildFragment() {
        return FragmentSynchronization.newInstance();
    }

    /**
     * Callback called when synchronization finished
     */
    @Override
    public void onSynchronisationFinished() {

    }
}
