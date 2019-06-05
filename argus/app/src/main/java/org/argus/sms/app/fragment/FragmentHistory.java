package org.argus.sms.app.fragment;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.database.Cursor;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.os.Bundle;
import android.support.v4.app.ListFragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;
import android.widget.Toast;
import fr.openium.androkit.utils.ToastUtils;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.adapter.AdapterHistory;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.HelperReportSent;

import java.util.ArrayList;

/**
 * History Fragment
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentHistory extends ListFragment implements LoaderManager.LoaderCallbacks<Cursor> {
    private final static String     TAG = FragmentHistory.class.getSimpleName();
    private final static boolean    DEBUG = true;

    private AdapterHistory mAdapter;

    private Toast                       mToast;
    private IFragmentHistoryListener    mListener;
    private Cursor                      mCursor;
    private Context                     mContext;

    /**
     * Listener for the Fragment history host
     */
    public interface IFragmentHistoryListener {
        public void onItemSelected(TypeSms type, String week, String month, String year, String timestamp);
    }

    /**
     * Create a new instance of FragmentHistory
     * @return a new FragementHistory
     */
    public static FragmentHistory newInstance() {
        FragmentHistory fragment = new FragmentHistory();
        return fragment;
    }

    /**
     * Mandatory empty constructor for the fragment manager to instantiate the
     * fragment (e.g. upon screen orientation changes).
     */
    public FragmentHistory() {
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        mAdapter = new AdapterHistory(getActivity().getApplicationContext(), this, null);
        setListAdapter(mAdapter);
        getLoaderManager().initLoader(0, null, this);
    }

    @Override
    public View onCreateView(final LayoutInflater inflater, final ViewGroup container, final Bundle savedInstanceState) {
        return inflater.inflate(org.argus.sms.app.R.layout.fragment_history, container, false);
    }

    @Override
    public void onViewCreated(final View view, final Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        getListView().setDividerHeight(0);
        getListView().setScrollbarFadingEnabled(false);
        getListView().setDivider(new ColorDrawable(Color.TRANSPARENT));
        getListView().setScrollBarStyle(View.SCROLLBARS_OUTSIDE_OVERLAY);
        int padding = mContext.getResources().getDimensionPixelSize(org.argus.sms.app.R.dimen.small_margin);
        getListView().setPadding(padding, 0, padding, 0);
    }

    @Override
    public void onAttach(Activity activity) {
        super.onAttach(activity);
        mContext = activity.getApplicationContext();
        try {
            mListener = (IFragmentHistoryListener) activity;
        } catch (ClassCastException e) {
            throw new ClassCastException(activity.toString()
                    + " must implement OnFragmentInteractionListener");
        }
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    @Override
    public void onListItemClick(ListView l, View v, int position, long id) {
        super.onListItemClick(l, v, position, id);
        if (null != mListener) {
            // Notify the active callbacks interface (the activity, if the
            // fragment is attached to one) that an item has been selected.
            if (mCursor.moveToPosition(position)) {
                TypeSms currentType = TypeSms.fromInt(mCursor.getInt(mCursor.getColumnIndex(SesContract.Sms.TYPE)));

                if (currentType == TypeSms.WEEKLY || currentType == TypeSms.MONTHLY ) {
                 getDataFromCursorAndGoToDetail(mCursor,currentType);
                } else if (currentType == TypeSms.THRESHOLD) {
                    TypeSms currentTypeHistoryDetails = TypeSms.fromInt(mCursor.getInt(mCursor.getColumnIndex(SesContract.Sms.TYPE_FOR_HISTORY_DETAILS)));
                    getDataFromCursorAndGoToDetail(mCursor,currentTypeHistoryDetails);
                }
            }
        }
    }

    private void getDataFromCursorAndGoToDetail(final Cursor cursor,TypeSms type) {
        String week = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.WEEK));
        String month = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.MONTH));
        String year = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.YEAR));
        String timestamp = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.TIMESTAMP));
        mListener.onItemSelected(type, week, month, year, timestamp);
    }


    @Override
    public Loader<Cursor> onCreateLoader(final int i, final Bundle bundle) {
        return new CursorLoader(getActivity().getApplicationContext(), SesContract.Sms.buildHistoryUri(), null, null, null, null);
    }

    @Override
    public void onLoadFinished(final Loader<Cursor> loader, final Cursor c) {
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "onLoadFinished ");
        }
        mAdapter.swapCursor(c);
        mCursor = c;
    }

    @Override
    public void onLoaderReset(final Loader<Cursor> loader) {

    }

    /**
     * Handle the click on a status view
     * @param view view clicked
     */
    public void onClickStatus(final View view) {
        int position = getListView().getPositionForView(view);
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "onClickStatus " + position);
        }
        if (mCursor.moveToPosition(position)) {
            TypeSms type = TypeSms.fromInt(mCursor.getInt(mCursor.getColumnIndex(SesContract.Sms.TYPE)));
            Status s = Status.fromInt(mCursor.getInt(mCursor.getColumnIndex(SesContract.Sms.STATUS)));
            if (type != TypeSms.ALERT) {
                String statusList = mCursor.getString(mCursor.getColumnIndex("statuslist"));
                s = HelperSms.getStatusFromStatusList(statusList);
            }
            if (s == Status.ERROR) {
                // Ask confirmation before resend a message

                // Following code deleted , asked by Guerra José (RE: Nouvelle version ARGUS : sent 05 May 2017 18:36)
                /*String week = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.WEEK));
                String month = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.MONTH));
                String year = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.YEAR));
                String timestamp = mCursor.getString(mCursor.getColumnIndex(SesContract.Sms.TIMESTAMP));

                ArrayList<Sms> list = HelperSms.getListOfSmsFromHistory(mContext, type, week, month, year, timestamp, true);

                String message = HelperSms.getFormatMessageForActionConfirmation(list);*/

                //displayConfirm(type, message);

                displayConfirm(type, "");
            }
        }
    }

    private void displayConfirm(final TypeSms type, String smsMessage) {
        String title = getString(org.argus.sms.app.R.string.send_report);
        String message = getString(org.argus.sms.app.R.string.history_ressend_confirmation, smsMessage);
        String okText = getString(org.argus.sms.app.R.string.send_report);
        String cancelText = getString(org.argus.sms.app.R.string.annuler);

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
                switch (type) {
                    case ALERT:
                        try {
                            HelperSms.resendSmsAndUpdateStatus(mContext, type, mCursor);
                        } catch (Exception e) {
                            mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, getActivity(), org.argus.sms.app.R.string.alert_not_sent, Toast.LENGTH_LONG, Gravity.CENTER);
                        }
                        break;
                    default:
                        HelperReportSent hrr = new HelperReportSent(mContext, type, mCursor);
                        getLoaderManager().initLoader(HelperReportSent.REPORT_SEND_LOADER, null, hrr);
                        break;
                }
            }
        });

        dialog.show();
        /*
        OKDialogFragmentMessage dialog = OKDialogFragmentMessage.newInstance(title, message, okText, cancelText, true);

        final FragmentHistory self = this;

        dialog.setListener(new OKDialogFragmentMessage.OKDialogMessageListener() {
            @Override
            public void onOkClicked(final DialogFragment dialogFragment) {
                switch (type) {
                    case ALERT:
                        try {
                            HelperSms.resendSmsAndUpdateStatus(mContext, type, mCursor);
                        } catch (Exception e) {
                            mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, getActivity(), org.argus.sms.app.R.string.alert_not_sent, Toast.LENGTH_LONG, Gravity.CENTER);
                        }
                        break;
                    default:
                        HelperReportSent hrr = new HelperReportSent(mContext, type, mCursor);
                        getLoaderManager().initLoader(HelperReportSent.REPORT_SEND_LOADER, null, hrr);
                        break;
                }
            }

            @Override
            public void onCancelClicked(final DialogFragment dialogFragment) {
                // nothing to do
            }
        });
        dialog.show(getFragmentManager(), "dialog");
        */
    }

    @Override
    public void onResume()
    {
        super.onResume();
        getLoaderManager().initLoader(0, null, this);
    }

}
