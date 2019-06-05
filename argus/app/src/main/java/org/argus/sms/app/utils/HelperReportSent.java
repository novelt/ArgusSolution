package org.argus.sms.app.utils;

import android.content.Context;
import android.database.Cursor;
import android.os.Bundle;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.util.Log;
import android.view.Gravity;
import android.widget.Toast;
import fr.openium.androkit.utils.ToastUtils;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.fragment.AbstractFragmentReport;
import org.argus.sms.app.fragment.FragmentHistoryDetail;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.provider.SesContract;
import java.util.ArrayList;


/**
 * Created by alexandre on 20/01/16.
 *
 * Class used to resend all sms for a given report.
 */
public class HelperReportSent implements LoaderManager.LoaderCallbacks<Cursor>{

    private final static String     TAG = FragmentHistoryDetail.class.getSimpleName();
    private final static boolean    DEBUG = true;

    // Id of the full report re-sent loader (used in FragmentHistory)
    public final static int        REPORT_SEND_LOADER = 2000;
    public final static int        REPORT_IS_SENT_LOADER = 2001;

    private Toast                   mToast;
    private TypeSms                 mTypeSms;
    private String                  mWeek;
    private String                  mMonth;
    private String                  mYear;
    private String                  mTimestamp = "";

    private Cursor                  mCursor;
    private Context                 mContext;
    private AbstractFragmentReport  mFragment = null;

    private boolean                 mAlreadyCalled = false;

    /**
     * Constructor for the class
     *
     * @param ctx the activity context
     * @param type the report type
     * @param cursor on the report
     */
    public HelperReportSent(Context ctx, TypeSms type, Cursor cursor) {
        this.mContext = ctx;
        this.mTypeSms = type;
        if (mTypeSms == TypeSms.WEEKLY)
            this.mWeek = cursor.getString(cursor.getColumnIndex(SesContract.Sms.WEEK));
        else
            this.mMonth = cursor.getString(cursor.getColumnIndex(SesContract.Sms.MONTH));
        this.mTimestamp = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TIMESTAMP));
    }

    /**
     *  Constructor for the class used for test if report is already in history
     *
     * @param ctx
     * @param type
     * @param weekOrMonth
     * @param year
     * @param timeStamp
     * @param afr
     */
    public HelperReportSent(Context ctx, TypeSms type, String weekOrMonth, String year, long timeStamp, AbstractFragmentReport afr) {
        this.mContext = ctx;
        this.mTypeSms = type;
        if (mTypeSms == TypeSms.WEEKLY)
            this.mWeek = weekOrMonth;
        else
            this.mMonth = weekOrMonth;

        if (timeStamp != 0) {
            this.mTimestamp = String.valueOf(timeStamp);
        }

        this.mFragment = afr;
        this.mYear = year;
    }

    /**
     * Called when a request is send with this class as callback
     * see LoaderCallbacks interface for more details
     */
    @Override
    public Loader<Cursor> onCreateLoader(final int id, final Bundle bundle) {
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "onCreateLoader id=" + id);
        }
        CursorLoader loader = null;
        if (id == REPORT_SEND_LOADER || id == REPORT_IS_SENT_LOADER) {
            String selection = null;
            String[] selectionArgs = null;
            if (mTypeSms == TypeSms.WEEKLY) {
                if (mTimestamp.isEmpty()) {
                    selection = SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.WEEK + "=? AND " + SesContract.Sms.STATUS + "!=? AND " + SesContract.Sms.YEAR + "=?";
                    selectionArgs = new String[]{String.valueOf(mTypeSms.toInt()), mWeek, String.valueOf(Status.DRAFT.toInt()), mYear};
                } else {
                    selection = SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.WEEK + "=? AND " + SesContract.Sms.STATUS + "!=? AND " + SesContract.Sms.TIMESTAMP + "=?";
                    selectionArgs = new String[]{String.valueOf(mTypeSms.toInt()), mWeek, String.valueOf(Status.DRAFT.toInt()), mTimestamp};
                }
            } else if (mTypeSms == TypeSms.MONTHLY) {
                if (mTimestamp.isEmpty()) {
                    selection = SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.MONTH + "=? AND " + SesContract.Sms.STATUS + "!=? AND " + SesContract.Sms.YEAR + "=?";
                    selectionArgs = new String[]{String.valueOf(mTypeSms.toInt()), mMonth, String.valueOf(Status.DRAFT.toInt()), mYear};
                } else {
                    selection = SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.MONTH + "=? AND " + SesContract.Sms.STATUS + "!=? AND " + SesContract.Sms.TIMESTAMP + "=?";
                    selectionArgs = new String[]{String.valueOf(mTypeSms.toInt()), mMonth, String.valueOf(Status.DRAFT.toInt()), mTimestamp};
                }
            }
            loader = new CursorLoader(mContext, SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, SesContract.Sms.TIMESTAMP + " DESC");
        }
        return loader;
    }

    /**
     * Callback when a request is finished
     * see LoaderCallbacks interface for more details
     */
    @Override
    public void onLoadFinished(final Loader<Cursor> cursorLoader, final Cursor cursor) {
        if (!mAlreadyCalled) {
            mCursor = cursor;
            if (cursorLoader != null) {
                if (BuildConfig.DEBUG && DEBUG) {
                    Log.d(TAG, "onLoadFinished cursorLoader.id=" + cursorLoader.getId());
                }
                if (cursorLoader.getId() == REPORT_SEND_LOADER) {
                    if (mCursor != null && mCursor.moveToFirst()) {
                        for (int i = 0; i < mCursor.getCount(); ++i) {
                            if (mCursor.moveToPosition(i)) {
                                Status s = Status.fromInt(mCursor.getInt(mCursor.getColumnIndex(SesContract.Sms.STATUS)));
                                if (s == Status.ERROR) {
                                    try {
                                        HelperSms.resendSmsAndUpdateStatus(mContext, mTypeSms, mCursor, i);
                                    } catch (Exception e) {
                                        mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, mContext, R.string.alert_not_sent, Toast.LENGTH_LONG, Gravity.CENTER);
                                    }
                                }
                            }
                        }
                    }
                } else if (cursorLoader.getId() == REPORT_IS_SENT_LOADER) {
                    if (mFragment != null) {
                        ArrayList<Sms> list = new ArrayList<>();

                        if (mCursor != null && mCursor.moveToFirst()) {
                            // Pass the list of already Sent messages
                            do {
                                list.add(new Sms(Config.getInstance(this.mContext), mCursor));
                            } while (cursor.moveToNext());

                            mFragment.setmAlreadySent(true, list);
                        } else {
                            mFragment.setmAlreadySent(false, list);
                        }
                    }
                }
            } else {
                if (BuildConfig.ERROR) {
                    Log.e(TAG, "onLoadFinished cursor null");
                }
            }
            mAlreadyCalled = true;
        }
    }

    @Override
    public void onLoaderReset(final Loader<Cursor> cursorLoader) {}

}
