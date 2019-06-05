package org.argus.sms.app.utils;

import android.util.Log;

import org.argus.sms.app.BuildConfig;

/**
 * Created by Olivier Goutet.
 * Openium 2015
 */
public class HelperArray {
    private final static String TAG = HelperArray.class.getSimpleName();
    private final static boolean DEBUG = true;

    public static boolean allItemsInArrayAre(final boolean[] data, final boolean expected) {
        for(boolean current : data) {
            if (current != expected){
                return false;
            }
        }
        if (BuildConfig.DEBUG && DEBUG){
            Log.d(TAG, "allItemsInArrayAre as expected");
        }
        return true;
    }

    public static boolean existItemInArray(final int[] data, final int expected) {
        for(int current : data) {
            if (current == expected){
                return true;
            }
        }
        if (BuildConfig.DEBUG && DEBUG){
            Log.d(TAG, "existItemInArray not found");
        }
        return false;
    }

}
