package org.argus.sms.app.activity;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.Gravity;
import android.widget.Toast;

import fr.openium.androkit.utils.ToastUtils;
import org.argus.sms.app.R;
import org.argus.sms.app.fragment.FragmentAlert;

/**
 * Specific activity used to display the Alert screen
 * @see android.app.Activity
 */
public class ActivityAlert extends AbstractActivityCancelOnBackUp implements FragmentAlert.IFragmentAlertListener {

    private Toast mToast;

    /**
     * Static method to get the {@link Intent} starting the ActivityAlert
     * @param ctx application context
     * @return Starting intent
     * @see android.content.Intent
     */
    public static Intent getStartingIntent(Context ctx) {
        Intent i = new Intent(ctx,ActivityAlert.class);
        return i;
    }


    @Override
    protected Fragment getChildFragment() {
        return FragmentAlert.newInstance();
    }


    /**
     * Hook when alert is sent
     */
    @Override
    public void onAlertSent() {

    }

    @Override
    protected void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        //Removed to get translation
        //getSupportActionBar().setTitle( Config.getInstance(this).getValueForKey(Config.KEYWORD_ALERT));
    }


    /**
     * Specific message showDraftToast
     */
    @Override
    protected void showDraftToast() {
        mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, this, R.string.alert_not_sent, Toast.LENGTH_LONG, Gravity.CENTER);
    }

    /**
     *
     */
    @Override
    public void onBackPressed() {
        quitAlert();
    }

    private void quitAlert() {
        final Activity self = this;

        new AlertDialog.Builder(this)
            .setIcon(android.R.drawable.ic_dialog_alert)
            .setTitle(getString(R.string.warning))
            .setMessage(getString(R.string.sure_quit_alert))
            .setPositiveButton(getString(R.string.yes), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    showDraftToast();
                    self.finish();
                }

            })
            .setNegativeButton(getString(R.string.no), null)
            .show();
    }
}
