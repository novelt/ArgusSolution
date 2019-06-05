package org.argus.sms.app.utils;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;

import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.provider.SesContract;

/**
 * Created by eotin on 03/10/2017.
 */

public class HelperHistory
{
    public static void checkHistoryStatus(Context context)
    {
        // Get all entry where the status is SENT (only SENT because in other case the server has gave an answer positive or negative...)
        String selection = SesContract.Sms.STATUS + "=?";
        String[] selectionArgs = {String.valueOf(Status.SENT.toInt())};

        Cursor cur = context.getContentResolver().query(SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, null);

        if (cur != null && cur.moveToFirst()) {
            do {
                Sms sms = new Sms(Config.getInstance(context), cur);

                if (HelperSms.hasSendingTimeoutExceeded(context, sms)) {
                    ContentValues cv = new ContentValues();
                    cv.put(SesContract.Sms.STATUS, Status.ERROR.toInt());
                    String updateSelection = SesContract.Sms.ID + "=?";
                    String[] updateSelectionArgs = {cur.getString(cur.getColumnIndex(SesContract.SmsColumns.ID))};
                    context.getContentResolver().update(SesContract.Sms.CONTENT_URI, cv, updateSelection, updateSelectionArgs);
                }

            } while (cur.moveToNext());

            cur.close();
        }
    }
}
