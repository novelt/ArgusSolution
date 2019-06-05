/**
 *
 */
package org.argus.sms.app.provider;

import android.content.ContentProvider;
import android.content.ContentValues;
import android.content.UriMatcher;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.net.Uri;
import android.provider.BaseColumns;
import android.util.Log;

import java.util.Arrays;

import fr.openium.androkit.database.SelectionBuilder;
import org.argus.sms.app.BuildConfig;

/**
 * ContentProvider of the Ses Application to access to the Database
 * @author Olivier Goutet
 * @author Openium - 2014
 */
public class SesProvider extends ContentProvider {
    private final static String TAG = SesProvider.class.getSimpleName();
    private final static boolean DEBUG = false;

    private SesDatabase mOpenHelper;

    private static final UriMatcher URI_MATCHER = buildUriMatcher();

    private static final int SMS = 100;
    private static final int SMS_BASEID = 101;
    private static final int SMS_HISTORY = 102;
    private static final int SMS_LAST_REPORTID = 103;

    /**
     * Build and return a {@link UriMatcher} that catches all {@link Uri} variations supported by this
     * {@link ContentProvider}.
     */
    private static UriMatcher buildUriMatcher() {
        final UriMatcher matcher = new UriMatcher(UriMatcher.NO_MATCH);
        final String authority = SesContract.CONTENT_AUTHORITY;

        // @formatter:off
        matcher.addURI(authority, SesContract.PATH_SMS + "/" + SesContract.Sms.PATH_BASEID + "/#", SMS_BASEID);
        matcher.addURI(authority, SesContract.PATH_SMS, SMS);
        matcher.addURI(authority, SesContract.PATH_SMS + "/" + SesContract.Sms.PATH_HISTORY, SMS_HISTORY);
        matcher.addURI(authority, SesContract.PATH_SMS + "/" + SesContract.Sms.PATH_REPORTID, SMS_LAST_REPORTID);

        // ------------------------------------------------------------------------------------------------------------
        // @formatter:on

        return matcher;
    }

    @Override
    public boolean onCreate() {
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "onCreate");
        }
        mOpenHelper = new SesDatabase(getContext());
        return true;
    }

    @Override
    public int delete(final Uri uri, final String selection, final String[] selectionArgs) {
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "delete uri= " + uri + " selection= " + selection + " selectionArgs= " + Arrays.toString(selectionArgs));
        }
        int count = 0;
        final SQLiteDatabase db = mOpenHelper.getWritableDatabase();
        final SelectionBuilder builder = buildSimpleSelection(uri);
        count = builder.where(selection, selectionArgs).delete(db);
        getContext().getContentResolver().notifyChange(uri, null);
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "delete count=" + count);
        }
        return count;
    }

    @Override
    public String getType(final Uri uri) {
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "getType");
        }
        final int match = URI_MATCHER.match(uri);
        switch (match) {
            case SMS:
                return SesContract.Sms.CONTENT_TYPE;
            case SMS_BASEID:
                return SesContract.Sms.CONTENT_ITEM_TYPE;
            default:
                throw new UnsupportedOperationException("Unknown uri: " + uri);
        }
    }

    @Override
    public Uri insert(final Uri uri, final ContentValues values) {

        final SQLiteDatabase db = mOpenHelper.getWritableDatabase();
        final int match = URI_MATCHER.match(uri);
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "insert uri=" + uri + " match=" + match);
        }
        switch (match) {
            case SMS: {
                long _id = db.insertOrThrow(SesDatabase.Tables.SMS, null, values);
                getContext().getContentResolver().notifyChange(uri, null);
                return SesContract.Sms.buildBaseIdUri(_id);
            }
            default:
                throw new UnsupportedOperationException("Unknown uri: " + uri);
        }
    }

    @Override
    public Cursor query(final Uri uri, final String[] projection, final String selection, final String[] selectionArgs, final String sortOrder) {
        final SQLiteDatabase db = mOpenHelper.getReadableDatabase();
        SelectionBuilder builder = null;
        Cursor c = null;
        final int match = URI_MATCHER.match(uri);
        if (match == SMS_HISTORY){
              c = db.rawQuery(QUERY_HISTORY,null);
        } else if (match == SMS_LAST_REPORTID){
            c = db.rawQuery(QUERY_LAST_REPORT_ID,null);
        } else {
            if (DEBUG && BuildConfig.DEBUG) {
                Log.d(TAG, "query uri=" + uri + " match=" + match);
            }
            builder = buildSimpleSelection(uri);
            builder.where(selection, selectionArgs);
            if (DEBUG && BuildConfig.DEBUG) {
                Log.d(TAG, "query " + builder.toString());
            }
            c = builder.query(db, projection, sortOrder);
        }
        c.setNotificationUri(getContext().getContentResolver(), uri);

        return c;
    }

    @Override
    public int update(final Uri uri, final ContentValues values, final String selection, final String[] selectionArgs) {
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "update uri=" + uri);
        }
        int count = 0;

        final SQLiteDatabase db = mOpenHelper.getWritableDatabase();

        final SelectionBuilder builder = buildSimpleSelection(uri);
        count = builder.where(selection, selectionArgs).update(db, values);
        getContext().getContentResolver().notifyChange(uri, null);
        // To notify the history list with the custom uri
        getContext().getContentResolver().notifyChange(SesContract.Sms.buildHistoryUri(),null);
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "update retVal=" + count);
        }
        return count;
    }

    /**
     * Build a simple {@link SelectionBuilder} to match the requested {@link Uri}. This is usually enough to support
     * {@link #insert}, {@link #update}, and {@link #delete} operations.
     */
    private SelectionBuilder buildSimpleSelection(final Uri uri) {

        final SelectionBuilder builder = new SelectionBuilder();
        final int match = URI_MATCHER.match(uri);
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "buildSimpleSelection uri=" + uri.toString() + " match=" + match);
        }
        switch (match) {
            case SMS: {
                builder.table(SesDatabase.Tables.SMS);
                break;
            }
            case SMS_BASEID: {
                //@formatter:off
                builder.table(SesDatabase.Tables.SMS)
                        .where(BaseColumns._ID + "=?", SesContract.Sms.getBaseIdUri(uri));
                //@formatter:on
                break;
            }
            default: {
                throw new UnsupportedOperationException("Unknown uri: " + uri);
            }
        }
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "buildSimpleSelection builder = " + builder);
        }
        return builder;
    }

    // Specific history query in database
   /* private String QUERY_HISTORY = "select * from (select *, group_concat(LABEL) as list, group_concat(STATUS) as statuslist from sms where type!=7 and type!=1 and STATUS!=4 group by WEEK, MONTH, YEAR, TIMESTAMP" +
            " union " +
            "select *, WEEK, STATUS from sms where type!=7 and type!=1 and YEAR IS NULL) " +
            "GROUP BY _ID  " +
            "order by YEAR DESC, MONTH DESC, WEEK DESC, TIMESTAMP DESC";
    */

    private String QUERY_HISTORY =
            "SELECT *, group_concat(LABEL) AS list, group_concat(STATUS) AS statuslist, type as TypeForHistoryDetails, " +
                    "CASE " +
                    "WHEN type = 6 THEN date(year || '-01-01', 'start of year', '+'|| (Month - 1) || ' months') " +
                    "WHEN type = 5 THEN date(year || '-01-01', 'start of year', '+'||  CASE WHEN ((7*(Week-1)) -3) > 0 THEN  ((7*(Week-1)) -3) ELSE 0 END || ' days'  , 'weekday 1') " +
                    "ELSE date(timestamp/1000, 'unixepoch') " +
                    "END " +
                    "AS DateForHistoryOrder " +
                    "FROM SMS " +
                    "WHERE  type!=7 and type != 1 and type != 8 and (STATUS!=4 or STATUS IS NULL)  " +
                    "GROUP BY YEAR, WEEK, MONTH, TIMESTAMP " +

                    // Convert SubType threshold into real threshold on a data base perspective
                    " UNION " +
                    "SELECT _id, NULL, NULL, WEEK, MONTH, YEAR, 8, 0, 0, SMSCONFIRM, TIMESTAMP, NULL, NULL, 0, SENDDATE, NULL, NULL, type, date(timestamp/1000, 'unixepoch') FROM SMS " +
                    "WHERE  subType = 4 " +

                    "ORDER BY DateForHistoryOrder desc, SENDDATE desc, Timestamp desc" ;


    private String QUERY_LAST_REPORT_ID = "select max (REPORTID) from SMS" ;

}
