package org.argus.gateway.ui;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;

import org.argus.gateway.App;

public class Main extends Activity {   
	
    private App app;
    
    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) {   
        super.onCreate(savedInstanceState);
        
        app = (App)getApplication();

        Intent i = new Intent(this, LogView.class);
        i.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);

        startActivity(i);
    }    
}