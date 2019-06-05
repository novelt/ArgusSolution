package org.argus.sms.app.activity;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.Configuration;
import android.os.Bundle;
import android.support.v7.app.ActionBarActivity;
import android.text.TextUtils;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.EditText;
import android.widget.Toast;

import fr.openium.androkit.sharedpreference.OKSharedPreferenceHelper;

import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.fragment.FragmentDashboard;
import org.argus.sms.app.fragment.FragmentPush;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.smsConfig.utility.ConfigSMSUtility;
import org.argus.sms.app.utils.HelperAlert;
import org.argus.sms.app.utils.HelperFile;
import org.argus.sms.app.utils.HelperHistory;
import org.argus.sms.app.utils.HelperPreference;


/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Activity displaying the dashboard of the application
 */
public class ActivityDashboard extends ActionBarActivity implements FragmentDashboard.IFragmentDashboardListener, FragmentPush.IFragmentPushListener
{
    private final static String TAG = ActivityDashboard.class.getSimpleName();
    private final static boolean DEBUG = true;

    @Override
    protected void onCreate(Bundle savedInstanceState)
    {

        if( getIntent().getBooleanExtra("Restart", false)){
            finish();
        }

        super.onCreate(savedInstanceState);

        // Set default Language
        String lang = HelperPreference.getLanguage(this);
        if (lang != null && !lang.isEmpty())
            HelperPreference.ChangeLanguage(this, lang);

        setContentView(R.layout.single_pane_container);
        if (savedInstanceState == null) {
            getSupportFragmentManager().beginTransaction()
                    .add(R.id.container, new FragmentDashboard())
                    .commit();
            //String messageInPreference = HelperPreference.getTextToDisplayInPushScreen(this);
            /*if (getIntent().hasExtra(ActivityPush.EXTRA_PUSH_TEXT) || !TextUtils.isEmpty(messageInPreference)){
                String text ;
                if (!TextUtils.isEmpty(messageInPreference)){
                    HelperPreference.saveTextToDisplayInPushScreen(this,null);
                    text = messageInPreference;
                }else {
                    text = getIntent().getStringExtra(ActivityPush.EXTRA_PUSH_TEXT);
                }
                Intent i = ActivityPush.getStartingIntent(this, text);
                startActivity(i);
            }*/
        }

        String hf = HelperPreference.getHFacility(this);
        if (hf != null && !hf.isEmpty()) {
            setTitle(hf);
        }

        // test if a synch was launch before...
        if (!HelperPreference.isAServerIsConfigured(this) || HelperPreference.getlastSync(this) == 0){
            //onClickSettings();
            openSettings();
        }

        // Check if all SMS in sent status have been updated after a reboot or an issue with the alarm manager
        HelperHistory.checkHistoryStatus(getApplicationContext());

        Config.IsTest = OKSharedPreferenceHelper.getBooleanFromSharedPreference(this, getString(R.string.prefs_test), false);
    }

    @Override
    protected void onResume()
    {
        super.onResume();
        CheckSettingsSmsConfig();
    }

    private void CheckSettingsSmsConfig()
    {
        try {
            // Must be at the beginning of any activity that can be called from an external intent
            Log.i(TAG, "Starting up, creating directories");
            HelperFile.createArgusDirs();

        } catch (Exception ex) {
           Log.e(TAG, "Error when creating directories", ex);
            return;
        }

        // Try to find Config SMS in the sms database
        ConfigSMSUtility configSMSUtility = ConfigSMSUtility.getInstance(getApplicationContext());

        if (configSMSUtility.isEnabled(getApplicationContext())) {
            Log.i(TAG, "Config SMS enable");
            configSMSUtility.readPendingConfigSMS(getApplicationContext());
            configSMSUtility.sendPendingAckSMS(getApplicationContext());
        } else {
            Log.i(TAG, "Config SMS disable");
        }
    }

    public boolean onCreateOptionsMenu(final Menu menu)
    {
        getMenuInflater().inflate(R.menu.menu_activity_dashboard, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item)
    {
        int id = item.getItemId();

        if (id == R.id.action_settings) {
            onClickSettings();
        }

        return super.onOptionsItemSelected(item);
    }

    /**
     * Open Dialog to enter password to access settings
     */
    private void onClickSettings()
    {

        // Lock settings with a password
        LayoutInflater li = LayoutInflater.from(this);
        View promptPassword = li.inflate(R.layout.prompt_password, null);
        final EditText typePassword = (EditText) promptPassword
                .findViewById(R.id.edit_Text_Dialog_Password);
        final Context mContext = this.getApplicationContext();

        new AlertDialog.Builder(this)
                .setView(promptPassword)
                .setTitle(R.string.action_settings)
                .setMessage(R.string.enter_password)
                .setNegativeButton(R.string.annuler, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                })
                .setPositiveButton(R.string.ok, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        String password = OKSharedPreferenceHelper.getStringFromSharedPreference(mContext, getString(R.string.prefs_settings_password), null);
                        if (password == null) {
                            password = BuildConfig.SETTINGS_PASSWORD ;
                        }

                        if (typePassword.getText().toString().equals(password)) {
                            dialog.cancel();
                            openSettings();
                        } else {
                            Toast.makeText(mContext, R.string.wrong_password, Toast.LENGTH_SHORT).show();
                        }
                    }

                })
                .show();
    }

    /**
     * Start the {@link ActivitySettings} if password is right
     */
    private void openSettings()
    {
        Intent i = new Intent(this, ActivitySettings.class);
        startActivity(i);
    }

    /**
     * Start the {@link ActivityReportWeekly} when the button is clicked
     */
    @Override
    public void startReportWeekly()
    {
        Intent i = ActivityReportWeekly.getStartingIntent(this);
        startActivity(i);

    }

    /**
     * Start the {@link ActivityReportMonthly} when the button is clicked
     */
    @Override
    public void startReportMonthly() {
        Intent i = ActivityReportMonthly.getStartingIntent(this);
        startActivity(i);
    }

    /**
     * Start the {@link ActivityAlert} when the button is clicked
     */
    @Override
    public void startAlert()
    {
        boolean externalArgusAlert = false ;

        if (HelperPreference.isExternalArgusAlertEnabled(getApplicationContext())) {

            try {
                externalArgusAlert = true ;
                startActivity(HelperAlert.getExternalAlertActivityIntent());

            } catch (Exception ex) {
                externalArgusAlert = false ;
                Log.e(TAG, "External Argus Alert activity not found", ex);

                Toast.makeText(getApplicationContext(), R.string.warning_external_argus_alert_not_found, Toast.LENGTH_SHORT).show();
            }
        }

        if (!externalArgusAlert &&
                Config.getInstance(getApplicationContext()).isConfigLoaded() &&
                !TextUtils.isEmpty(Config.getInstance(getApplicationContext()).getValueForKey(Config.KEYWORD_ALERT)) ) { // Use internal Alert activity
            Intent i = ActivityAlert.getStartingIntent(this);
            startActivity(i);
        }
    }

    /**
     * Start the {@link ActivityHistory} when the button is clicked
     */
    @Override
    public void startHistory()
    {
        Intent i = ActivityHistory.getStartingIntent(this);
        startActivity(i);
    }

    @Override
    public void onHistoryClicked()
    {
    }

    @Override
    public void onSkipClicked()
    {
    }

    @Override
    public void onConfigurationChanged(Configuration newConfig)
    {
        Intent intent = getIntent();
        finish();
        startActivity(intent);
        super.onConfigurationChanged(newConfig);
    }
}
