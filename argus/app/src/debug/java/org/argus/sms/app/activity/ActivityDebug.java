package org.argus.sms.app.activity;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

import org.argus.sms.app.utils.HelperPreference;
import org.argus.sms.app.utils.HelperReminder;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Activity displaying history
 */
public class ActivityDebug extends Activity implements View.OnClickListener {


    private Button mButtonPush;
    private Button mButtonPush2;

    @Override
    protected void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(org.argus.sms.app.R.layout.activity_debug);
        mButtonPush = (Button) findViewById(org.argus.sms.app.R.id.activity_debug_Button_push);
        mButtonPush.setOnClickListener(this);
        mButtonPush2 = (Button) findViewById(org.argus.sms.app.R.id.activity_debug_Button_push2);
        mButtonPush2.setOnClickListener(this);
    }

    @Override
    public void onClick(final View v) {
        if (v == mButtonPush){
            onClickPush();
        }else if (v == mButtonPush2){
            onClickPush2();
        }
    }

    private void onClickPush() {
        HelperReminder.displayReminderNotificationIfNeeded(this, "Message test", 12, 0, true);
    }
    private void onClickPush2() {
        HelperPreference.saveTextToDisplayInPushScreen(this, "Coucou");
        Intent i = new Intent(this, ActivityDashboard.class);
        startActivity(i);
    }
}
