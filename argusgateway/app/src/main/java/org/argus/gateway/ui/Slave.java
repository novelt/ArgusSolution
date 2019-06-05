package org.argus.gateway.ui;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.Bundle;
import android.preference.Preference;
import android.preference.PreferenceActivity;
import android.preference.PreferenceScreen;
import android.view.Menu;

import org.argus.gateway.App;
import org.argus.gateway.R;

public class Slave extends PreferenceActivity  {
    
    private App app;
    
    private BroadcastReceiver installReceiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {  
            updateInstallStatus();
        }
    };    
    
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        addPreferencesFromResource(R.xml.slaves);
        
        app = (App) getApplication();        
        
        IntentFilter installReceiverFilter = new IntentFilter();        
        installReceiverFilter.addAction(App.SLAVES_CHANGED_INTENT);
        registerReceiver(installReceiver, installReceiverFilter);        
        
        updateInstallStatus();
    }    
    
    @Override
    public void onDestroy()
    {        
        unregisterReceiver(installReceiver);
        
        super.onDestroy();
    }    
    
    public void updateInstallStatus()
    {
        PreferenceScreen screen = this.getPreferenceScreen();
        int numPrefs = screen.getPreferenceCount();
        
        String basePackageName = app.getPackageName();
        
        this.setTitle(getText(R.string.slaves_title)+" ("+app.getOutgoingMessageLimit()+")");
        
        for(int i=0; i < numPrefs; i++)
        {
            Preference p = screen.getPreference(i);
            String packageNum = p.getKey();
            String packageName = basePackageName + "." + packageNum;
            
            if (app.isSmsSlaveInstalled(packageName)) {
                p.setSummary("Installed.");
            } else {
                p.setSummary("Not installed.\nInstall to increase limit by " + app.OUTGOING_SMS_MAX_COUNT + " SMS per "
                        + (app.OUTGOING_SMS_CHECK_PERIOD > 1805000 ? "hour..." : "half an hour..."));
            }
        }
    }
    
    @Override
    public boolean onPrepareOptionsMenu(Menu menu) {
        //this.finish();
        return true;
    }    
}
