package org.argus.gateway.ui;

import android.app.ProgressDialog;
import android.content.SharedPreferences;
import android.content.BroadcastReceiver;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.Context;
import android.content.SharedPreferences.OnSharedPreferenceChangeListener;
import android.os.Bundle;
import android.preference.*;
import android.provider.Settings;
import android.provider.Settings.SettingNotFoundException;
import android.text.method.PasswordTransformationMethod;
import android.view.Menu;

import org.argus.gateway.App;
import org.argus.gateway.R;
import org.argus.gateway.task.ServerFinderTask;

public class Prefs extends PreferenceActivity implements OnSharedPreferenceChangeListener {

    private App app;
    private Context     mContext;
    
    private BroadcastReceiver installReceiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {  
            PreferenceScreen screen = getPreferenceScreen();
            updatePrefSummary(screen.findPreference("send_limit"));
        }
    };        
    
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        addPreferencesFromResource(R.xml.prefs);

        mContext = this;

        app = (App) getApplication();
        
        PreferenceScreen screen = this.getPreferenceScreen();
        int numPrefs = screen.getPreferenceCount();
        
        for(int i=0; i < numPrefs;i++)
        {
            updatePrefSummary(screen.getPreference(i));
        }
        
        IntentFilter installReceiverFilter = new IntentFilter();        
        installReceiverFilter.addAction(App.SLAVES_CHANGED_INTENT);
        registerReceiver(installReceiver, installReceiverFilter);

        // TODO Added to avoid crash on create intent from xml pref
        findPreference("help").setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(final Preference preference) {
                Intent i = new Intent(app, Help.class);
                startActivity(i);
                return true;
            }
        });
        findPreference("send_limit").setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(final Preference preference) {
                Intent i = new Intent(app, Slave.class);
                startActivity(i);
                return true;
            }
        });
        findPreference("test_numbers").setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(final Preference preference) {
                Intent i = new Intent(app, TestPhoneNumbers.class);
                startActivity(i);
                return true;
            }
        });
        findPreference("ignored_numbers").setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(final Preference preference) {
                Intent i = new Intent(app, IgnoredPhoneNumbers.class);
                startActivity(i);
                return true;
            }
        });
        // Used to locate server
        findPreference("find_server_url").setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(final Preference preference) {
                ProgressDialog progDialog = ProgressDialog.show(mContext,
                        "",
                        "Try to locate server, please wait.", true);
                ServerFinderTask sft = new ServerFinderTask(mContext, progDialog);
                sft.execute();
                return true;
            }
        });
        Preference pref = findPreference("keep_in_inbox");
        if (pref != null) {
            getParent(pref).removePreference(pref);
        }
    }

    /**
     * Get parent(category) of a preference
     * @param preference
     * @return
     */
    private PreferenceGroup getParent(Preference preference)
    {
        return getParent(getPreferenceScreen(), preference);
    }

    /**
     * Get parent(category) of a preference
     * @param preference
     * @return
     */
    private PreferenceGroup getParent(PreferenceGroup root, Preference preference)
    {
        for (int i = 0; i < root.getPreferenceCount(); i++)
        {
            Preference p = root.getPreference(i);
            if (p == preference)
                return root;
            if (PreferenceGroup.class.isInstance(p))
            {
                PreferenceGroup parent = getParent((PreferenceGroup)p, preference);
                if (parent != null)
                    return parent;
            }
        }
        return null;
    }

    @Override
    public void onDestroy()
    {        
        unregisterReceiver(installReceiver);        
        super.onDestroy();
    }    
    
    @Override 
    protected void onResume(){
        super.onResume();
        // Set up a listener whenever a key changes             
        getPreferenceScreen().getSharedPreferences().registerOnSharedPreferenceChangeListener(this);
    }

    @Override 
    protected void onPause() { 
        super.onPause();
        // Unregister the listener whenever a key changes             
        getPreferenceScreen().getSharedPreferences().unregisterOnSharedPreferenceChangeListener(this);     
    } 

    public void onSharedPreferenceChanged(SharedPreferences sharedPreferences, String key) { 
        
        if (key.equals("outgoing_interval"))
        {            
            app.setOutgoingMessageAlarm();
        }
        else if (key.equals("wifi_sleep_policy"))
        {
            int value;
            String valueStr = sharedPreferences.getString("wifi_sleep_policy", "screen");
            if ("screen".equals(valueStr))
            {
                value = Settings.System.WIFI_SLEEP_POLICY_DEFAULT;
            }
            else if ("plugged".equals(valueStr))
            {
                value = Settings.System.WIFI_SLEEP_POLICY_NEVER_WHILE_PLUGGED;
            }
            else 
            {
                value = Settings.System.WIFI_SLEEP_POLICY_NEVER;
            }
            
            Settings.System.putInt(getContentResolver(), 
                Settings.System.WIFI_SLEEP_POLICY, value);
        }
        else if (key.equals("server_url"))
        {
            String serverUrl = sharedPreferences.getString("server_url", "");
            
            // assume http:// scheme if none entered
            if (serverUrl.length() > 0 && !serverUrl.contains("://"))
            {
                sharedPreferences.edit()
                    .putString("server_url", "http://" + serverUrl)
                    .commit();
            }
            
            app.log("Server URL changed to: " + app.getDisplayString(app.getServerUrl()));
        }
        else if (key.equals("call_notifications"))
        {
            app.log("Call notifications changed to: " + (app.callNotificationsEnabled() ? "ON": "OFF"));
        }
        else if (key.equals("phone_number"))
        {
            app.log("Phone number changed to: " + app.getDisplayString(app.getPhoneNumber()));
        }
        else if (key.equals("test_mode"))
        {
            app.log("Test mode changed to: " + (app.isTestMode() ? "ON": "OFF"));
        }        
        else if (key.equals("password"))
        {
            app.log("Password changed");
        }
        else if (key.equals("nb_line_logview"))
        {
            String valueStr = sharedPreferences.getString("nb_line_logview", "" + app.MAX_DISPLAYED_LOG);
            app.log("Number of line in log view changed to: " + valueStr + " Characters");
            app.MAX_DISPLAYED_LOG = Integer.parseInt(valueStr);
        }
        else if (key.equals("logfile_size"))
        {
            String valueStr = sharedPreferences.getString("logfile_size", "" + app.LOG_SIZE);
            app.log("Size of log file changed to: " + valueStr + "Mo");
            app.LOG_SIZE = Integer.parseInt(valueStr);
        }
        else if (key.equals("android_id_field"))
        {
            String valueStr = sharedPreferences.getString("android_id_field", "[empty]");
            app.log("Default message ID Key changed to: " + valueStr);
            app.KEY_MESSAGE_ID = valueStr;
        }
        else if (key.equals("enabled")) {
            app.log(app.isEnabled() ? getText(R.string.started) : getText(R.string.stopped));
            app.enabledChanged();
        }
        
        sendBroadcast(new Intent(App.SETTINGS_CHANGED_INTENT));
        updatePrefSummary(findPreference(key));
    }    

    private void updatePrefSummary(Preference p)
    {
        if (p == null)
        {
            return;
        }
        
        String key = p.getKey();
        
        if ("wifi_sleep_policy".equals(key))
        {       
            int sleepPolicy;
            
            try
            {
                sleepPolicy = Settings.System.getInt(this.getContentResolver(), 
                    Settings.System.WIFI_SLEEP_POLICY);                
            }
            catch (SettingNotFoundException ex)
            {
                sleepPolicy = Settings.System.WIFI_SLEEP_POLICY_DEFAULT;
            }               
            
            switch (sleepPolicy)
            {
                case Settings.System.WIFI_SLEEP_POLICY_DEFAULT:
                    p.setSummary("Wi-Fi will disconnect when the phone sleeps");
                    break;
                case Settings.System.WIFI_SLEEP_POLICY_NEVER_WHILE_PLUGGED:
                    p.setSummary("Wi-Fi will disconnect when the phone sleeps unless it is plugged in");
                    break;
                case Settings.System.WIFI_SLEEP_POLICY_NEVER:
                    p.setSummary("Wi-Fi will stay connected when the phone sleeps");
                    break;
            }
        }    
        else if ("send_limit".equals(key))
        {
            int limit = app.getOutgoingMessageLimit();
            String limitStr = "Send up to " + limit + " SMS per "
                    + (app.OUTGOING_SMS_CHECK_PERIOD > 1805000 ? "hour." : "half an hour.");
            
            if (limit < 300)
            {
                limitStr += "\nClick to increase limit...";
            }
            
            p.setSummary(limitStr);
        }
        // Refresh the server url after a successful auto discovery.
        else if ("server_url".equals(key))
        {
            String serverUrl = getPreferenceScreen().getSharedPreferences().getString("server_url", "");

            p.setSummary(serverUrl);
        }
        else if ("help".equals(key))
        {
            p.setSummary(app.getPackageInfo().versionName);
        }
        else if (p instanceof PreferenceCategory)
        {
            PreferenceCategory category = (PreferenceCategory)p;
            int numPreferences = category.getPreferenceCount();
            for (int i = 0; i < numPreferences; i++)
            {
                updatePrefSummary(category.getPreference(i));
            }                    
        }
        else if (p instanceof ListPreference) {
            p.setSummary(((ListPreference)p).getEntry()); 
        }
        else if (p instanceof EditTextPreference) {
            
            EditTextPreference textPref = (EditTextPreference)p;
            String text = textPref.getText();
            if (text == null || text.equals(""))
            {            
                p.setSummary("(not set)"); 
            }            
            else if (textPref.getEditText().getTransformationMethod() instanceof PasswordTransformationMethod)
            {
                p.setSummary("********");
            }
            else
            {
                p.setSummary(text);
            }
        }
    }    

    // any other time the Menu key is pressed
    @Override
    public boolean onPrepareOptionsMenu(Menu menu) {
        // TODO Remove this function to avoid quit settings
        //this.finish();
        return (true);
    }
}
