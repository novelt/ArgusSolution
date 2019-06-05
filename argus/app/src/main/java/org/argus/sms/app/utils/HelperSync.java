package org.argus.sms.app.utils;

import android.content.Context;

import java.text.SimpleDateFormat;
import java.util.Date;

import org.argus.sms.app.R;

/**
 * Synchronisation helper
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class HelperSync {

    private final static String DATE_FORMAT = "dd/MM/yyyy HH:mm";

    /**
     * Get the synchronisation in progress text
     * @param ctx application context
     * @param smsCount count of sms
     * @return Message to display
     */
    public static String getSyncInProgressText(Context ctx, int smsCount){
        SimpleDateFormat sdf = new SimpleDateFormat(DATE_FORMAT);
        long timestamp = HelperPreference.getSyncStartDate(ctx);
       return ctx.getString(R.string.sync_in_progress_since, sdf.format(new Date(timestamp)),smsCount);
    }

    /**
     * Get the last synchronisation text
     * @param ctx application context
     * @return Message to display
     */
    public static String getLastSyncText(Context ctx){
        SimpleDateFormat sdf = new SimpleDateFormat(DATE_FORMAT);
        long timestamp = HelperPreference.getlastSync(ctx);
        return ctx.getString(R.string.last_sync, sdf.format(new Date(timestamp)));
    }
}
