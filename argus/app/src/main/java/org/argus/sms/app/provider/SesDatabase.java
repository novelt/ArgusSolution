package org.argus.sms.app.provider;

import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.provider.BaseColumns;
import android.util.Log;

import com.readystatesoftware.sqliteasset.SQLiteAssetHelper;

import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.utils.EventSyncFinished;
import org.argus.sms.app.utils.HelperPreference;
import org.argus.sms.app.utils.OttoSingleton;

import java.util.Date;

/**
 * Database of the application
 * @author Olivier Goutet
 * Openium - 2014
 *
 * https://github.com/jgilfelt/android-sqlite-asset-helper
 */
public class SesDatabase extends SQLiteAssetHelper {
    private final static String TAG = SesDatabase.class.getSimpleName();
    private final static boolean DEBUG = true;

    private static final String DATABASE_NAME = "omsses.db";
    private static final int DATABASE_VERSION = 11;

    public interface Tables {
        String SMS = "SMS";

    }

    public SesDatabase(final Context context) {
        super(context, DATABASE_NAME, null, DATABASE_VERSION);

        if (isDataBaseAlreadySynchronized()) { // if Database is synchronized
            if (HelperPreference.getlastSync(context) == 0) { // if there is no preference date of last synch
                String id = HelperPreference.getSmsIdAndInc(context);
                HelperPreference.saveWaitingSyncId(context, Integer.parseInt(id));
                HelperPreference.saveLastSync(context, new Date().getTime());
                int synchId =  HelperPreference.getWaitingSyncId(context);
                HelperPreference.saveLastSyncId(context, synchId);
                HelperPreference.clearCurrentSyncData(context);
                OttoSingleton.getInstance().getBus().post(new EventSyncFinished());
            }
        }


        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "SesDatabase");
        }
    }

    private boolean isDataBaseAlreadySynchronized()
    {
        boolean synch;

        SQLiteDatabase db = this.getReadableDatabase();

        Cursor cursor = db.rawQuery("SELECT * FROM " + Tables.SMS + " WHERE " + SesContract.SmsColumns.TYPE + " = " + TypeSms.CONFIG.toInt(), null);
        if (cursor != null && cursor.getCount() >= 1) {
            synch =  true;
        } else {
            synch = false ;
        }

        if (cursor != null) {
            cursor.close();
        }

        return synch;
    }

    /*/**
     * Create the tables of the application (only one in that case)
     * @param db database to create the table into
     */
    /*private void createTables(final SQLiteDatabase db) {
        final StringBuilder sb = new StringBuilder();

        sb.append("CREATE TABLE " + Tables.SMS + " (");
        sb.append(BaseColumns._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, ");
        sb.append(SesContract.SmsColumns.DISEASE + " TEXT, ");
        sb.append(SesContract.SmsColumns.LABEL + " TEXT, ");
        sb.append(SesContract.SmsColumns.WEEK + " TEXT, ");
        sb.append(SesContract.SmsColumns.MONTH + " TEXT, ");
        sb.append(SesContract.SmsColumns.YEAR + " TEXT, ");
        sb.append(SesContract.SmsColumns.TYPE + " INTEGER, ");
        sb.append(SesContract.SmsColumns.SUBTYPE + " INTEGER, ");
        sb.append(SesContract.SmsColumns.ID + " INTEGER DEFAULT -1, ");
        sb.append(SesContract.SmsColumns.TEXT + " TEXT, ");
        sb.append(SesContract.SmsColumns.TIMESTAMP + " TEXT, ");
        sb.append(SesContract.SmsColumns.STATUS + " INT, ");
        sb.append(SesContract.SmsColumns.SMSCONFIRM + " TEXT, ");
        sb.append(SesContract.SmsColumns.REPORTID + " INT,");
        sb.append(SesContract.SmsColumns.SENDDATE + " TEXT);");
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "onCreate " + sb.toString());
        }
        db.execSQL(sb.toString());
        sb.setLength(0);

    }*/

    @Override
    public void onUpgrade(final SQLiteDatabase db, final int oldVersion, final int newVersion) {
        if (DEBUG && BuildConfig.DEBUG) {
            Log.d(TAG, "onUpgrade");
        }

        switch (oldVersion) {
            case 1 :
            case 2 :
            case 3 :
            case 4 :
            case 5 :
            case 6 :
            case 7 :
            case 8 :
                db.execSQL("DROP TABLE IF EXISTS " + Tables.SMS);
                onCreate(db);
                break ;
            case 9 :
            case 10:
                updateDataBase(db);
                break ;
        }

    }

    public static String getDBName() { return DATABASE_NAME; }

    /**
     * Update the Database
     *
     * @param db
     */
    private void updateDataBase(SQLiteDatabase db)
    {
        // Update Version 10
        boolean columnExist = isColumnExists(db, Tables.SMS, SesContract.SmsColumns.REPORTID);
        if (!columnExist) {
            db.execSQL("ALTER TABLE " + Tables.SMS + " ADD COLUMN " + SesContract.SmsColumns.REPORTID + " INT;");
        }

        // Update Version 11
        columnExist = isColumnExists(db, Tables.SMS, SesContract.SmsColumns.SENDDATE);
        if (!columnExist) {
            db.execSQL("ALTER TABLE " + Tables.SMS + " ADD COLUMN " + SesContract.SmsColumns.SENDDATE + " TEXT;");
        }

    }

    /**
     * Check if column already exists
     *
     * @param db
     * @param table
     * @param column
     * @return
     */
    private boolean isColumnExists(SQLiteDatabase db, String table, String column)
    {
        Cursor cursor = db.rawQuery("PRAGMA table_info("+ table +")", null);
        if (cursor != null) {
            while (cursor.moveToNext()) {
                String name = cursor.getString(cursor.getColumnIndex("name"));
                if (column.equalsIgnoreCase(name)) {
                    return true;
                }
            }
        }

        return false;
    }

}
