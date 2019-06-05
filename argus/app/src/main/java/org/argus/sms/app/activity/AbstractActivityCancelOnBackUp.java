package org.argus.sms.app.activity;

import android.view.MenuItem;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Abstract class to factorize the cancel draft behaviour when the home button is clicked
 */
public abstract class AbstractActivityCancelOnBackUp extends AbstractActivitySesSms {

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle item selection
        switch (item.getItemId()) {
            case android.R.id.home:
                onBackPressed();
                return true;
        }
        return super.onOptionsItemSelected(item);
    }

    /**
     * Abstract method defined in subclass to display a message when activity canceled
     */
    protected abstract void showDraftToast();
}
