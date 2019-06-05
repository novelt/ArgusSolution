package org.argus.sms.app.fragment;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.database.Cursor;
import android.os.Bundle;
import android.support.v4.app.DialogFragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.List;
import java.util.Locale;

import butterknife.ButterKnife;
import butterknife.InjectView;
import butterknife.Optional;
import fr.openium.androkit.dialog.OKDialogFragmentMessage;
import fr.openium.androkit.utils.ToastUtils;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperReportSent;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.HelperCalendar;
import org.argus.sms.app.utils.HelperReport;
import org.argus.sms.app.view.ViewWeekFromTo;

/**
 * History detail Fragment
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentHistoryDetail extends AbstractFragment implements LoaderManager.LoaderCallbacks<Cursor>, View.OnClickListener {
    private final static String TAG = FragmentHistoryDetail.class.getSimpleName();
    private final static boolean DEBUG = true;

    private static final String KEY_TYPE = "KEY_TYPE";
    private static final String KEY_WEEK = "KEY_WEEK";
    private static final String KEY_MONTH = "KEY_MONTH";
    private static final String KEY_YEAR = "KEY_YEAR";
    private static final String KEY_TIMESTAMP = "KEY_TIMESTAMP";
    private static final int    LOADER_DATA = 23;
    private static final String KEY_BASEID = "KEY_BASEID";

    private Toast   mToast;
    private TypeSms mTypeSms;
    private String  mWeek;
    private String  mMonth;
    private String  mYear;
    private String  mTimestamp;

    //@InjectView(R.id.fragment_history_detail_TextView_title)
    //protected TextView mTextViewEpidemiologique;
    @Optional
    @InjectView(R.id.fragment_history_detail_TextView_calendar)
    protected TextView mTextViewCalendar;
    @InjectView(R.id.fragment_history_detail_LinearLayout)
    protected LinearLayout mLinearLayout;
    @Optional
    @InjectView(R.id.fragment_history_detail_ViewWeekFromTo)
    protected ViewWeekFromTo mViewWeekFromTo;
    private Cursor mCursor;


    /**
     * Create a new instance of FragmentHistoryDetail configured with the params value
     * @param type type of sms
     * @param week week of the history detail
     * @param month month of the history detail
     * @param year year of the history detail
     * @param timestamp timestamp of the history detail
     * @return a new FragmentHistoryDetail
     */
    public static FragmentHistoryDetail newInstance(TypeSms type, String week, String month, String year, String timestamp) {
        FragmentHistoryDetail fragment = new FragmentHistoryDetail();
        Bundle args = new Bundle();
        args.putInt(KEY_TYPE, type.toInt());
        args.putString(KEY_WEEK, week);
        args.putString(KEY_MONTH, month);
        args.putString(KEY_YEAR, year);
        args.putString(KEY_TIMESTAMP, timestamp);
        fragment.setArguments(args);
        return fragment;
    }

    public FragmentHistoryDetail() {
        // Required empty public constructor
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            mTypeSms = TypeSms.fromInt(getArguments().getInt(KEY_TYPE, 0));
            mWeek = getArguments().getString(KEY_WEEK);
            mMonth = getArguments().getString(KEY_MONTH);
            mYear = getArguments().getString(KEY_YEAR);
            mTimestamp = getArguments().getString(KEY_TIMESTAMP);
        }
        setHasOptionsMenu(true);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        int layout = 0;
        if (mTypeSms == TypeSms.WEEKLY) {
            layout = R.layout.fragment_historydetail_weekly;
        } else {
            layout = R.layout.fragment_historydetail_monthly;
        }
        View rootView = inflater.inflate(layout, container, false);
        ButterKnife.inject(this, rootView);
        initViewFromStartConfig(mTypeSms, mWeek, mMonth, mYear);
        getLoaderManager().initLoader(LOADER_DATA, null, this);
        return rootView;
    }

    /**
     * Init the view with the initial config
     * @param type Type of sms
     * @param week week of the history detail
     * @param month month of the history detail
     * @param year year of the history detail
     */
    private void initViewFromStartConfig(final TypeSms type, final String week, final String month, final String year) {
        Calendar c = HelperCalendar.getCalendarWithCorrectStartWeek(mContext);

        if (type == TypeSms.WEEKLY) {
            c.clear();
            c.set(Calendar.YEAR, Integer.parseInt(year));
            c.set(Calendar.WEEK_OF_YEAR, Integer.parseInt(week));
            Date d = c.getTime();
            mViewWeekFromTo.displayDate(mContext,d);

        } else if (type == TypeSms.MONTHLY) {
            c.set(Calendar.YEAR, Integer.parseInt(year));
            int monthNumber = Integer.parseInt(month);
            if (monthNumber >= 1 && monthNumber <= 12) { // Month Number 0 = JANUARY
                c.set(Calendar.MONTH, monthNumber - 1);
            } else {
                c.set(Calendar.MONTH, 0); // this case would never happen
            }
            SimpleDateFormat simpleDateFormat = new SimpleDateFormat("MMMM yyyy", Locale.getDefault());
            mTextViewCalendar.setText(simpleDateFormat.format(c.getTime()));
        }
    }


    @Override
    public Loader<Cursor> onCreateLoader(final int id, final Bundle bundle) {
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "onCreateLoader id=" + id);
        }
        CursorLoader loader = null;
        if (id == LOADER_DATA) {
            String selection = null;
            String[] selectionArgs = null;
            if (mTypeSms == TypeSms.WEEKLY) {
                selection = SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.WEEK + "=? AND " + SesContract.Sms.STATUS + "!=? AND " + SesContract.Sms.TIMESTAMP + "=?";
                selectionArgs = new String[]{String.valueOf(mTypeSms.toInt()), mWeek, String.valueOf(Status.DRAFT.toInt()), mTimestamp};
            } else if (mTypeSms == TypeSms.MONTHLY) {
                selection = SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.MONTH + "=? AND " + SesContract.Sms.STATUS + "!=? AND " + SesContract.Sms.TIMESTAMP + "=?";
                selectionArgs = new String[]{String.valueOf(mTypeSms.toInt()), mMonth, String.valueOf(Status.DRAFT.toInt()), mTimestamp};
            }
            loader = new CursorLoader(mContext, SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, SesContract.Sms.DISEASE + " ASC");
        }
        return loader;
    }

    @Override
    public void onLoadFinished(final Loader<Cursor> cursorLoader, final Cursor cursor) {
        mCursor = cursor;
        if (cursorLoader != null && cursor != null) {
            if (BuildConfig.DEBUG && DEBUG) {
                Log.d(TAG, "onLoadFinished cursorLoader.id=" + cursorLoader.getId() + " cursor.getCount()=" + mCursor.getCount());
            }
            if (cursorLoader.getId() == LOADER_DATA) {
                if (mCursor != null && mCursor.moveToFirst()) {
                    mLinearLayout.removeAllViews();
                    do {
                        String sms = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.TEXT));
                        String disease = Parser.getFieldFromSms(Config.KEYWORD_DISEASE, sms, Config.getInstance(mContext));
                        String label = Parser.getSpacedFieldFromSms(Config.KEYWORD_LABEL, sms, Config.getInstance(mContext));
                        if (label == null)
                            label = disease;
                        List<Parser.ConfigField> listData = Parser.getOtherFieldsFromSms(sms, Config.getInstance(mContext));
                        Status s = Status.fromInt(mCursor.getInt(mCursor.getColumnIndex(SesContract.Sms.STATUS)));
                        SubTypeSms subType = SubTypeSms.fromInt(cursor.getInt(cursor.getColumnIndex(SesContract.Sms.SUBTYPE)));
                        String smsConfirm = cursor.getString(cursor.getColumnIndex(SesContract.Sms.SMSCONFIRM));
                        HelperReport.addItem(false, getActivity(), mLinearLayout, label, listData, s,subType,smsConfirm, String.valueOf(mCursor.getPosition()), this);
                    } while (mCursor.moveToNext());
                }
            }
        } else {
            if (BuildConfig.ERROR) {
                Log.e(TAG, "onLoadFinished cursor null");
            }
        }
    }

    @Override
    public void onLoaderReset(final Loader<Cursor> cursorLoader) {

    }


    @Override
    public void onClick(final View view) {
        if (view.getTag() instanceof String) {
            String positionString = (String) view.getTag();
            int position = Integer.parseInt(positionString);
            if (mCursor.moveToPosition(position)) {
                Status s = Status.fromInt(mCursor.getInt(mCursor.getColumnIndex(SesContract.Sms.STATUS)));
                TypeSms type = TypeSms.fromInt(mCursor.getInt(mCursor.getColumnIndex(SesContract.Sms.TYPE)));

                if (BuildConfig.DEBUG && DEBUG) {
                    Log.d(TAG, "onClick status =" + s);
                }
                if (s == Status.ERROR) {
                    if (BuildConfig.DEBUG && DEBUG) {
                        Log.d(TAG, "onClick send");
                    }

                    // Following code deleted , asked by Guerra José (RE: Nouvelle version ARGUS : sent 05 May 2017 18:36)
                    /*String week = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.WEEK));
                    String month = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.MONTH));
                    String year = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.YEAR));
                    String timestamp = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.TIMESTAMP));

                    ArrayList<Sms> list = HelperSms.getListOfSmsFromHistory(mContext, type, week, month, year, timestamp, true);

                    String message = HelperSms.getFormatMessageForActionConfirmation(list);

                    displayConfirm(message);*/
                    displayConfirm("");
                }
            }
        }
    }



    private void displayConfirm(String smsMessage) {
        String title = getString(R.string.send_report);
        String message = getString(R.string.history_ressend_confirmation, smsMessage);
        String okText = getString(R.string.send_report);
        String cancelText = getString(R.string.annuler);

        AlertDialog dialog = new AlertDialog.Builder(this.getContext()).create();

        dialog.setTitle(title);
        dialog.setMessage(message);
        dialog.setButton(DialogInterface.BUTTON_NEGATIVE, cancelText, new DialogInterface.OnClickListener() {

            public void onClick(DialogInterface dialog, int which) {
                // Write your code here to execute after dialog    closed
            }
        });

        dialog.setButton(DialogInterface.BUTTON_POSITIVE, okText, new DialogInterface.OnClickListener() {

            public void onClick(DialogInterface dialog, int which) {
                try {
                    HelperReportSent hrr = new HelperReportSent(mContext, mTypeSms, mCursor);
                    getLoaderManager().initLoader(HelperReportSent.REPORT_SEND_LOADER, null, hrr);
                } catch (Exception e) {
                    mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, getActivity(), R.string.report_not_sent, Toast.LENGTH_LONG, Gravity.CENTER);
                }
            }
        });

        dialog.show();
        /*OKDialogFragmentMessage dialog = OKDialogFragmentMessage.newInstance(title,message,okText,cancelText,true);

        dialog.setListener(new OKDialogFragmentMessage.OKDialogMessageListener() {
            @Override
            public void onOkClicked(final DialogFragment dialogFragment) {
                try {
                    HelperSms.resendSmsAndUpdateStatus(mContext, mTypeSms, mCursor);
                } catch (Exception e) {
                    mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, getActivity(), R.string.report_not_sent, Toast.LENGTH_LONG, Gravity.CENTER);
                }
            }

            @Override
            public void onCancelClicked(final DialogFragment dialogFragment) {
                // nothing to do
            }
        });
        dialog.show(getFragmentManager(), "dialog");*/
    }

}
