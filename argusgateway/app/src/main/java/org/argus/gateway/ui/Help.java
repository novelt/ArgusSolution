package org.argus.gateway.ui;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.DialogInterface.OnClickListener;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.text.Html;
import android.view.View;
import android.widget.TextView;

import org.argus.gateway.App;

public class Help extends Activity {

    private App app;
    
    @Override
    public void onCreate(Bundle icicle) {
        super.onCreate(icicle);
        
        setContentView(org.argus.gateway.R.layout.help);
        
        TextView help = (TextView) this.findViewById(org.argus.gateway.R.id.help);
                
        app = (App)getApplication();

        String html = "<b>"+getText(org.argus.gateway.R.string.app_name)+" " + app.getPackageInfo().versionName + "</b><br /><br />";
        
        help.setText(Html.fromHtml(html));                        
        
    }
    
    public void resetClicked(View v)
    {        
        new AlertDialog.Builder(this)
            .setTitle("Are you sure?")
            .setPositiveButton("Yes", 
                new OnClickListener() {
                    public void onClick(DialogInterface dialog, int which)
                    {
                        PreferenceManager.getDefaultSharedPreferences(app)                            
                            .edit()
                            .clear()
                            .commit();
                        
                        app.enabledChanged();
                        
                        dialog.dismiss();
                        
                        finish();
                    }
                }
            )
            .setNegativeButton("Cancel", 
                new OnClickListener() {
                    public void onClick(DialogInterface dialog, int which)
                    {
                        dialog.dismiss();
                    }
                }
            )
            .show();
    }    
}
