package org.argus.sms.app.utils;

import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;

/**
 * Created by eotin on 08/11/2017.
 */

public class HelperAlert {

    private static final String ARGUS_ALERT_PACKAGE = "org.argus.alert.android";
    private static final String ARGUS_ALERT_CLASS = "org.odk.collect.android.activities.SplashScreenActivity";

    /**
     * Create and return the Argus Alert application intent
     *
     * @return Intent
     */
    public static Intent getExternalAlertActivityIntent()
    {
        Intent intent = new Intent(Intent.ACTION_MAIN);
        ComponentName cn = new ComponentName(
                ARGUS_ALERT_PACKAGE,
                ARGUS_ALERT_CLASS);
        intent.setComponent(cn);

        return intent;
    }

    /**
     * Test if Argus Alert application is installed
     *
     * @param context
     * @return boolean
     */
    public static boolean isArgusAlertApplicationInstalled(Context context)
    {
        PackageManager packageManager = context.getPackageManager();

        try {
            packageManager.getPackageInfo(ARGUS_ALERT_PACKAGE, 0);
            return true;
        } catch (PackageManager.NameNotFoundException ex) {
            return false;
        }
    }
}
