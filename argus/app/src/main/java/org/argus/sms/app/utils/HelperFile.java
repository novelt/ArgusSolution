package org.argus.sms.app.utils;

import android.os.Environment;

import java.io.File;

/**
 * Created by eotin on 16/08/2017.
 */

public class HelperFile
{
    public static final String NOVELT_ROOT = Environment.getExternalStorageDirectory() + File.separator + ".novelt";
    public static final String ARGUS_ROOT = NOVELT_ROOT + File.separator + "argus";
    public static final String CONFIG_SMS_QUEUE_PATH = ARGUS_ROOT + File.separator + "configSMS.txt";

    public static void createArgusDirs() throws RuntimeException
    {
        String cardStatus = Environment.getExternalStorageState();
        if (!cardStatus.equals(Environment.MEDIA_MOUNTED)) {
            throw new RuntimeException("Cannot Access to the SD Card");
        }

        String[] dirs = {
                NOVELT_ROOT, ARGUS_ROOT
        };

        for (String dirName : dirs) {
            File dir = new File(dirName);
            if (!dir.exists()) {
                if (!dir.mkdirs()) {
                    RuntimeException e =
                            new RuntimeException("ARGUS : Cannot create directory: "
                                    + dirName);
                    throw e;
                }
            } else {
                if (!dir.isDirectory()) {
                    RuntimeException e =
                            new RuntimeException("ARGUS : " + dirName
                                    + " exists, but is not a directory");
                    throw e;
                }
            }
        }
    }
}
