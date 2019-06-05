package org.argus.sms.app.activity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;

import org.argus.sms.app.fragment.FragmentPush;

/**
 * Created by Olivier Goutet.
 * Openium 2015
 *
 * Activity displaying the push notification in fullscreen
 */
public class ActivityPush extends AbstractActivitySesSms implements FragmentPush.IFragmentPushListener {

    public final static String EXTRA_PUSH_TEXT = "EXTRA_PUSH_TEXT";

    /**
     * Getter for the starting {@link Intent} of the Activity
     * @param ctx Application context
     * @return starting intent of the activity
     * @see Intent
     */
    public static Intent getStartingIntent(Context ctx, String text) {
        Intent i = new Intent(ctx,ActivityPush.class);
        i.putExtra(EXTRA_PUSH_TEXT,text);
        return i;
    }

    @Override
    protected void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
    }

    @Override
    protected Fragment getChildFragment() {
        return FragmentPush.newInstance(getIntent().getStringExtra(EXTRA_PUSH_TEXT));
    }

    @Override
    public void onHistoryClicked() {
        Intent i = ActivityHistory.getStartingIntent(this);
        startActivity(i);
        finish();
    }

    @Override
    public void onSkipClicked() {
        finish();
    }
}
