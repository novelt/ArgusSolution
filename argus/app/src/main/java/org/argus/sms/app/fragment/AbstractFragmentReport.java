package org.argus.sms.app.fragment;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.ContentProviderOperation;
import android.content.ContentValues;
import android.database.Cursor;
import android.net.Uri;
import android.os.Bundle;
import android.support.v4.content.Loader;
import android.telephony.SmsManager;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.Toast;
import android.content.DialogInterface;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;

import butterknife.ButterKnife;
import fr.openium.androkit.utils.ToastUtils;
import hugo.weaving.DebugLog;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.activity.AbstractActivitySesSms;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.model.TypeData;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.HelperArray;
import org.argus.sms.app.utils.HelperPreference;
import org.argus.sms.app.utils.HelperReminder;
import org.argus.sms.app.utils.HelperReport;
import org.argus.sms.app.utils.HelperReportSent;
import org.argus.sms.app.utils.HelperSmsSender;
import org.argus.sms.app.utils.UpdateAsyncQueryHandler;
import org.argus.sms.app.view.OKDialogProgress;
import org.argus.sms.app.view.ViewReportItemSummary;

/**
 * Abstract fragment for all reports fragments
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public abstract class AbstractFragmentReport extends AbstractFragmentSend implements View.OnClickListener {
    private final static String TAG = AbstractFragmentReport.class.getSimpleName();
    private final static boolean DEBUG = true;

    private boolean mAllDataValid;
    private int mCurrentCount = 0;
    private OKDialogProgress mProgressDialog;
    // boolean used to know if the current week report was already sent once...
    protected boolean mAlreadySent = false;
    protected ArrayList<Sms> mAlreadySentList = new ArrayList<>();
    protected boolean mNoNetworkAlreadyDisplay = false;

    /**
     * Specific listener for fragment reports
     */
    public interface IFragmentReportListener {
        public void onReportSent();

        public void onStartDetail(TypeSms type, String desease, String label);

        public void onShowCalendar();
    }

    protected IFragmentReportListener mListener;

    public AbstractFragmentReport() {
        // Required empty public constructor
    }

    @Override
    public void onAttach(Activity activity) {
        super.onAttach(activity);
        try {
            mListener = (IFragmentReportListener) activity;
        } catch (ClassCastException e) {
            throw new ClassCastException(activity.toString()
                    + " must implement OnFragmentInteractionListener");
        }
    }

    /**
     * Start the loader for clean data
     */
    @Override
    public void loadConfigClean() {
        getLoaderManager().restartLoader(LOADER_CLEAN, null, this);
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);
        //loadConfigData();
        loadConfigDraft();
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View mainView = inflater.inflate(R.layout.fragment_report_monthly, container, false);
        ButterKnife.inject(this, mainView);
        return mainView;
    }


    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    @Override
    public void onSendClicked() {
        if (isDateConfigured() && mAllDataValid) {
            getLoaderManager().initLoader(LOADER_CONFIRM, null, this);
        } else {
            ToastUtils.displayToastAndCancelOldIfExists(mToast, mContext, R.string.missing_fields, Toast.LENGTH_LONG, Gravity.CENTER);
        }
    }

    /**
     * Display the confirmation dialogue before sending the entire report
     * If user want to send an exact already sent report (Only last sent report for the same week) :
     * - If this last report has error in history, only send errors messages (like via the history view)
     * - If this last report was totally sent without any error, do not re send the report and go back to the main screen.
     *
     * @param listSms list of sms that will be sent
     */
    private void displayConfirm(List<Sms> listSms, List<Sms> listSmsPreviouslySent) {
        String title = getString(R.string.send_report);
        String message = getConfirmMessage(listSms);
        message = HelperSms.shortenSmsConfirmMessage(mContext, message);

        boolean identicalReportSent = false;
        boolean historicReportContainsSMSOnError = false;
        List<Sms> onlyLastSentSmsReport = HelperSms.getOnlyLastSentSmsReport(listSmsPreviouslySent);

        if (mAlreadySent) {
            message = getString(R.string.warning_already_sent) + "\n" + message;
            // Check if the last sent report is exactly the same as this report
            identicalReportSent = HelperSms.compareReports(listSms, onlyLastSentSmsReport);
            // Check if the last sent report contains some error messages
            historicReportContainsSMSOnError = HelperSms.containsOnErrorSms(onlyLastSentSmsReport);
        }

        if (Config.IsTest) {
            message = getString(R.string.warning_test_mode) + "\n" + message;
        }

        if (identicalReportSent) {
            if (!historicReportContainsSMSOnError) {
                informReportAlreadySent();
            } else {
                Sms firstSms = onlyLastSentSmsReport.get(0);
                if (firstSms != null) {
                    informReSendFromHistory(firstSms, message);
                }
            }

            //loadConfigClean(); // To clean current edition
            //getActivity().finish();

        } else {
            AlertDialog dialog = new AlertDialog.Builder(this.getContext()).create();
            dialog.setTitle(title);
            dialog.setMessage(message);
            dialog.setButton(DialogInterface.BUTTON_NEGATIVE, getString(R.string.annuler), new DialogInterface.OnClickListener() {

                public void onClick(DialogInterface dialog, int which) {
                    // Write your code here to execute after dialog    closed
                    Toast.makeText(mContext, getString(R.string.sms_not_sent), Toast.LENGTH_SHORT).show();
                }
            });

            dialog.setButton(DialogInterface.BUTTON_POSITIVE, getString(R.string.send_report), new DialogInterface.OnClickListener() {

                public void onClick(DialogInterface dialog, int which) {
                    // Write your code here to execute after dialog    closed
                    loadSend();
                }
            });

            dialog.show();
        }
    }

    /**
     * Inform user that a same report has already been sent
     */
    private void informReportAlreadySent() {
        AlertDialog dialog = new AlertDialog.Builder(this.getContext()).create();
        dialog.setTitle(getString(R.string.warning_identical_report_title));
        dialog.setMessage(getString(R.string.warning_identical_report_message));
        dialog.setButton(DialogInterface.BUTTON_NEUTRAL, getString(R.string.ok), new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int which) {
                loadConfigClean(); // To clean current edition
                Toast.makeText(mContext, getString(R.string.sms_not_sent), Toast.LENGTH_SHORT).show();
                getActivity().finish(); // Exit
            }
        });

        dialog.show();
    }

    /**
     * DO NOT inform user that a same report has already been sent but is on error. Use same message as if user send a normal report.
     * BEHIND, ONLY messages on error will be sent again
     *
     * @param sms
     * @param message
     */
    private void informReSendFromHistory(final Sms sms, String message) {
        AlertDialog dialog = new AlertDialog.Builder(this.getContext()).create();
        dialog.setTitle(getString(R.string.warning_identical_report_title));
        // dialog.setMessage(getString(R.string.warning_identical_report_send_history_message));
        dialog.setMessage(message);
        dialog.setButton(DialogInterface.BUTTON_NEGATIVE, getString(R.string.annuler), new DialogInterface.OnClickListener() {

            public void onClick(DialogInterface dialog, int which) {
                Toast.makeText(mContext, getString(R.string.sms_not_sent), Toast.LENGTH_SHORT).show();
            }
        });

        dialog.setButton(DialogInterface.BUTTON_POSITIVE, getString(R.string.send_report), new DialogInterface.OnClickListener() {

            public void onClick(DialogInterface dialog, int which) {
                loadConfigClean(); // To clean current edition
                loadSendFromHistory(sms.mType, sms.mYear, sms.mWeek, sms.mMonth, sms.mTimestamp);
                displayOkAndExit();
            }
        });

        dialog.show();
    }

    /**
     * Send error messages from history
     *
     * @param type
     * @param year
     * @param week
     * @param month
     * @param timeStamp
     */
    public abstract void loadSendFromHistory(TypeSms type, String year, String week, String month, long timeStamp);

    /**
     * Abstract function that return the confirmation message in the sub-class
     *
     * @param listSms list of all sms to send.
     * @return
     */
    public abstract String getConfirmMessage(List<Sms> listSms);

    /**
     * Is the date configured in the Fragment
     *
     * @return true if configured, false otherwise
     */
    protected abstract boolean isDateConfigured();

    /**
     * Get the container for items to display
     *
     * @return
     */
    protected abstract LinearLayout getItemsContainer();

    /**
     * Set the calendar text from the specified date
     *
     * @param selectedDate date to save
     */
    abstract public void setCalendarNumber(final Date selectedDate);

    @DebugLog
    @Override
    public void onClick(final View view) {
        if (view.getTag() != null && view.getTag() instanceof String) {
            mListener.onStartDetail(getTypeSms(), (String) view.getTag(), ((ViewReportItemSummary) view.getParent()).GetTitle());
        }
    }

    /**
     * Callback when a request is finish
     * This callback is called to :
     * get configuration for the report                                    : LOADER_CONFIG
     * get base message for each disease                                   : LOADER_DRAFT
     * get confirmation message before sending the report                  : LOADER_CONFIRM
     * send the report                                                     : LOADER_SEND
     * clean the generated message when leaving the page without sending   : LOADER_CLEAN
     *
     * @param cursorLoader
     * @param cursor
     */
    // TODO Refactor this function because it's clearly the most un-readable part of this application
    @Override
    public void onLoadFinished(final Loader<Cursor> cursorLoader, final Cursor cursor) {
        if (cursorLoader != null && cursor != null) {
            if (BuildConfig.DEBUG && DEBUG) {
                Log.d(TAG, "onLoadFinished cursorLoader.id=" + cursorLoader.getId() + " cursor.getCount()=" + cursor.getCount());
            }
            if (cursorLoader.getId() == LOADER_CONFIG) {
                if (cursor != null && cursor.moveToFirst()) {
                    List<Sms> list = createSmsListFromCursor(cursor);
                    getLoaderManager().destroyLoader(LOADER_CONFIG);
                    insertSmsListInDatabase(list);
                    loadConfigDraft();
                } else {
                    getLoaderManager().destroyLoader(LOADER_CONFIG);
                }
            } else if (cursorLoader.getId() == LOADER_DRAFT) {
                if (cursor != null && cursor.moveToFirst()) {
                    getItemsContainer().removeAllViews();
                    mAllDataValid = true;
                    loadDateFromCursor(cursor);

                    HashMap<String, String> smsList = new HashMap<>();
                    do {
                        String sms = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));
                        String disease = Parser.getFieldFromSms(Config.KEYWORD_DISEASE, sms, Config.getInstance(mContext));
                        smsList.put(sms, disease);
                    } while (cursor.moveToNext());

                    cursor.moveToFirst();
                    do {
                        String sms = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));
                        String disease = Parser.getFieldFromSms(Config.KEYWORD_DISEASE, sms, Config.getInstance(mContext));

                        if (smsList.containsKey(sms)) {
                            List<String> diseaseSms = new ArrayList<>();
                            Iterator it = smsList.entrySet().iterator();
                            while (it.hasNext()) {
                                Map.Entry<String, String> pair = (Map.Entry) it.next();
                                if (pair.getValue().equals(disease)) {
                                    diseaseSms.add(pair.getKey());
                                    it.remove();
                                }
                            }
                            Collections.reverse(diseaseSms);
                            // Part added to support label in report
                            String label = Parser.getSpacedFieldFromSms(Config.KEYWORD_LABEL, sms, Config.getInstance(mContext));
                            if (label == null)
                                label = disease;

                            List<Parser.ConfigField> listData = new ArrayList<>();

                            for (String str : diseaseSms) {
                                listData.addAll(Parser.getOtherFieldsFromSmsReport(str, Config.getInstance(mContext)));
                            }
                            boolean areDataValid = HelperReport.areDataValid(listData);

                            Status s;
                            if (areDataValid) {
                                s = Status.SENT;
                            } else {
                                mAllDataValid = false;
                                s = Status.fromInt(cursor.getInt(cursor.getColumnIndex(SesContract.Sms.STATUS)));
                            }
                            SubTypeSms subType = SubTypeSms.fromInt(cursor.getInt(cursor.getColumnIndex(SesContract.Sms.SUBTYPE)));
                            String smsConfirm = cursor.getString(cursor.getColumnIndex(SesContract.Sms.SMSCONFIRM));
                            HelperReport.addItem(true, getActivity(), getItemsContainer(), label, listData, s, subType, smsConfirm, disease, this);
                        }
                    } while (cursor.moveToNext());
                } else {
                    getLoaderManager().destroyLoader(LOADER_DRAFT);
                    loadConfigData();

                }
                checkConnectivityToSendSms(!mNoNetworkAlreadyDisplay, null);
                mNoNetworkAlreadyDisplay = true;

            } else if (cursorLoader.getId() == LOADER_SEND) {
                getLoaderManager().destroyLoader(LOADER_DRAFT);
                if (cursor != null && cursor.moveToFirst()) {

                    getView().post(new Runnable() {
                        @Override
                        public void run() {
                            mProgressDialog = OKDialogProgress.newInstance(getString(getSendingMessage()), true);
                            mProgressDialog.show(getFragmentManager(), "progress");
                        }
                    });

                    mCurrentCount = 0;
                    final int totalCount = cursor.getCount();
                    final boolean[] results = new boolean[totalCount];
                    final int[] errors = new int[totalCount];
                    final long timeStamp = new Date().getTime();
                    final List<Sms> smsList = new ArrayList<>();

                    for (int i = 0; i < cursor.getCount(); ++i) {
                        cursor.moveToPosition(i);
                        smsList.add(
                                new Sms(Config.getInstance(mContext),
                                        cursor
                                )
                        );
                    }

                    getLoaderManager().destroyLoader(LOADER_SEND);

                    int position = 0;

                    HelperSmsSender.SmsListener listener = new HelperSmsSender.SmsListener<Sms>() {
                        @Override
                        public void onSmsSentSuccess(final String tag, final Sms sms) {
                            int position = Integer.parseInt(tag);
                            manageSenderListenerResult(position, sms, Status.SENT, timeStamp, results, errors, 0);
                            mCurrentCount++;
                            manageNextSmsToBeSent(mCurrentCount, position, totalCount, smsList, results, errors, this);
                        }

                        @Override
                        public void onSmsSentError(final String tag, final Sms sms, final int error) {
                            int position = Integer.parseInt(tag);
                            manageSenderListenerResult(position, sms, Status.ERROR, timeStamp, results, errors, error);
                            mCurrentCount++;
                            manageNextSmsToBeSent(mCurrentCount, position, totalCount, smsList, results, errors, this);
                        }
                    };

                    // Take the first SMS of the list
                    Sms sms = smsList.get(0);
                    if (sms == null) {
                        return ;
                    }

                    HelperSmsSender.sendSms(mContext, getTypeSms(), sms, listener, String.valueOf(position));
                }
            } else if (cursorLoader.getId() == LOADER_CONFIRM) {
                if (cursor != null && cursor.moveToFirst()) {

                    final ArrayList<Sms> list = new ArrayList<Sms>();
                    do {
                        Sms sms = new Sms(Config.getInstance(mContext),cursor);
                        sms.mYear = String.valueOf(getSelectedYear());
                        sms.mWeek = String.valueOf(getSelectedWeek());
                        sms.mMonth = String.valueOf(getSelectedMonth());

                        list.add(sms);
                    } while (cursor.moveToNext());

                    UpdateSmsBeforeSend(list);

                    // Destroy de loader immediately
                    getLoaderManager().destroyLoader(LOADER_CONFIRM);

                    Log.v(TAG, "" + HelperPreference.getNetworkLock(getContext()));
                    DialogInterface.OnClickListener listener = new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialogInterface, int i) {
                            getView().post(new Runnable() {
                                @Override
                                public void run() {
                                    if (!HelperPreference.getNetworkLock(getContext()))
                                        displayConfirm(list, mAlreadySentList);
                                }
                            });
                        }
                    };

                    if (!checkConnectivityToSendSms(true, listener)) {
                        getView().post(new Runnable() {
                            @Override
                            public void run() {
                                displayConfirm(list, mAlreadySentList);
                            }
                        });
                    }
                }
            } else if (cursorLoader.getId() == LOADER_CLEAN) { // Clean the not send report message to avoid old report in database and get a blank report next time
                if (cursor != null && cursor.moveToFirst()) {
                    ArrayList<ContentProviderOperation> list = new ArrayList<ContentProviderOperation>();
                    for (int i = 0; i < cursor.getCount(); ++i) {
                        cursor.moveToPosition(i);
                        long id = cursor.getLong(cursor.getColumnIndex(SesContract.Sms._ID));
                        Uri uri = SesContract.Sms.buildBaseIdUri(id);
                        list.add(ContentProviderOperation.newDelete(uri).build());
                    }
                    try {
                        mContext.getContentResolver().applyBatch(SesContract.CONTENT_AUTHORITY, list);
                    } catch (Exception e) {
                        if (BuildConfig.ERROR) {
                            Log.e(TAG, "checkIfAllReceiveCallbackAreFinished ");
                            e.printStackTrace();
                        }
                    }
                }
            }
        } else {
            if (BuildConfig.ERROR) {
                Log.e(TAG, "onLoadFinished cursor null");
            }
        }
    }

    private void UpdateSmsBeforeSend(ArrayList<Sms> list)
    {
        if (list != null && list.size() > 0) {
            for (int i = 0; i < list.size(); i++) {
                ContentValues cv = list.get(i).getContentValues(Config.getInstance(mContext));
                new UpdateAsyncQueryHandler(mContext.getContentResolver()).startUpdate(
                        12, null,
                        SesContract.Sms.CONTENT_URI,
                        cv,
                        SesContract.getDetailsSelectionId(),
                        SesContract.getDetailsSelectionArgsId(list.get(i).mType, Status.DRAFT, list.get(i).mDisease, list.get(i).mId));
            }
        }
    }


    protected abstract int getSelectedYear() ;
    protected abstract int getSelectedMonth() ;
    protected abstract int getSelectedWeek() ;

    /**
     * Manage result when SMS is sent
     *
     * @param position
     * @param sms
     * @param status
     * @param timeStamp
     * @param results
     * @param errors
     * @param error
     */
    protected void manageSenderListenerResult(int position,
                                              Sms sms,
                                              Status status,
                                              long timeStamp,
                                              boolean[] results,
                                              int[] errors,
                                              int error)
    {
        sms.mStatus = status;
        ContentValues cv = new ContentValues();
        cv.put(SesContract.Sms.STATUS, status.toInt());
        cv.put(SesContract.Sms.TIMESTAMP, timeStamp);

        if (status == Status.SENT) {
            sms.mSendDate = new Date().getTime();
            cv.put(SesContract.Sms.SENDDATE, sms.mSendDate);
        }

        Uri uri = SesContract.Sms.buildBaseIdUri(sms._Id);

        ArrayList<ContentProviderOperation> list = new ArrayList<>();
        list.add(ContentProviderOperation.newUpdate(uri).withValues(cv).build());

        try {
            mContext.getContentResolver().applyBatch(SesContract.CONTENT_AUTHORITY, list);
        } catch (Exception ex) {
            Log.e(TAG, "try to applyBatch when sending SMS");
        }

        if (status == Status.SENT) {
            HelperReminder.setUpReminderNotConfirmed(mContext, getTypeSms(), uri);
            results[position] = true;
        } else {
            results[position] = false;
            errors[position] = error;
        }
    }

    /**
     * Check if all SMS have been sent. If not, send next one.
     *
     * @param currentCount
     * @param position
     * @param totalCount
     * @param smsList
     * @param results
     * @param errors
     * @param listener
     */
    protected void manageNextSmsToBeSent(final int currentCount,
                                         int position,
                                         int totalCount,
                                         List<Sms> smsList,
                                         boolean[] results,
                                         int[] errors,
                                         HelperSmsSender.SmsListener listener)
    {
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, String.format("manageNextSmsToBeSent currentCount=%d / %d", currentCount, totalCount));
        }

        if (currentCount == totalCount) {
            finishSendingReportOperation(results, errors, mProgressDialog);
        } else {
            position++;
            Sms nextSms = smsList.get(position);
            HelperSmsSender.sendSms(getContext(), getTypeSms(), nextSms, listener, String.valueOf(position));
        }
    }

    protected void finishSendingReportOperation(final boolean[] results, final int[] errors, final OKDialogProgress dialog) {
        try {
            //if no messages have been sent
            if (HelperArray.allItemsInArrayAre(results, false)) {
                if (HelperArray.existItemInArray(errors, SmsManager.RESULT_ERROR_GENERIC_FAILURE)) {
                    HelperSmsSender.displayGsmNetworkErrorText(mContext);
                } else {
                    HelperSmsSender.displayStandardErrorText(mContext);
                }

                try {
                    dialog.dismiss();
                } catch (Exception ex) {
                    // for some reason the dialog dismiss crash with error : Can not perform this action after onSaveInstanceState
                }

            } else { // some messages have been sent
                //mContext.getContentResolver().applyBatch(SesContract.CONTENT_AUTHORITY, list);
                if (!HelperArray.allItemsInArrayAre(results, true)) {
                    // some sms have failed
                    displayNotOkAndExit();
                } else {
                    // all sms have been sent successfully
                    displayOkAndExit();
                }
            }
        } catch (Exception e) {
            if (BuildConfig.ERROR) {
                Log.e(TAG, "checkIfAllReceiveCallbackAreFinished ");
                e.printStackTrace();
            }
        }

    }

    /**
     * Load the date from the cursor
     *
     * @param cursor cursor to load the date from
     */
    protected abstract void loadDateFromCursor(final Cursor cursor);

    /**
     * Add all the sms in the display list
     *
     * @param list list of sms to display
     */
    private void insertSmsListInDatabase(final List<Sms> list) {
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "insertSmsListInDatabase list.size()" + list.size());
        }

        int reportId = ((AbstractActivitySesSms) this.getActivity()).getReportId();

        for (Sms sms : list) {
            ContentValues cv = sms.getContentValues(Config.getInstance(mContext));
            cv.put(SesContract.Sms.ID, HelperPreference.getSmsIdAndInc(mContext));

            if (sms.mReportId == 0) {
                cv.put(SesContract.Sms.REPORTID, reportId);
            }

            getActivity().getContentResolver().insert(SesContract.Sms.CONTENT_URI, cv);
        }
    }

    /**
     * Build the list of sms from the given cursor
     *
     * @param cursor cursor to load the sms from
     * @return List of SMS
     */
    private List<Sms> createSmsListFromCursor(final Cursor cursor) {
        List<Sms> list = new ArrayList<Sms>();
        do {
            Sms sms = new Sms();
            String text = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));

            sms.mDisease = Parser.getFieldFromSms(Config.KEYWORD_DISEASE, text, Config.getInstance(mContext));
            sms.mLabel = Parser.getSpacedFieldFromSms(Config.KEYWORD_LABEL, text, Config.getInstance(mContext));

            List<Parser.ConfigField> listData = Parser.getOtherFieldsFromSmsReport(text, Config.getInstance(mContext));
            List<Parser.Constraint> listConstraint = Parser.getConstraintsFromSmsReport(text, Config.getInstance(mContext));

            int reportId = ((AbstractActivitySesSms) this.getActivity()).getReportId();
            int keyWordId = HelperPreference.getSmsId(mContext);

            addSmsInList(sms, listData, listConstraint, list, String.valueOf(keyWordId).length(), String.valueOf(reportId).length());

        } while (cursor.moveToNext());
        return list;
    }

    /**
     * Add sms in the sms draft list, if a sms-template is to big to be send in one this function will cut it in multiple sms
     *
     * @param smsTemplate the base sms
     * @param listData    list of field to add at the sms
     * @param list        the list of sms
     */
    private void addSmsInList(Sms smsTemplate,
                              List<Parser.ConfigField> listData,
                              List<Parser.Constraint> listConstraint,
                              List<Sms> list,
                              int nbCharsKeyWordId,
                              int nbCharsReportId) {
        int maxChar = HelperPreference.getConfigSmsCharCount(mContext);
        Config conf = Config.getInstance(null);
        int baseCharCount = smsTemplate.toSms(getTypeSms(), Config.getInstance(null), true).length();
        baseCharCount += (nbCharsKeyWordId -1);
        baseCharCount += (nbCharsReportId -1);

        String valueForYear = conf.getValueForKey(Config.KEYWORD_YEAR);

        int charCount = baseCharCount
                + (Parser.SEPARATOR_EQUALS).length() * 2
                + (Parser.SEPARATOR_COMMA).length() * 3
                + (valueForYear != null ? valueForYear.length() + 4 : 4);

        if (getTypeSms() == TypeSms.WEEKLY) {
            charCount += conf.getValueForKey(Config.KEYWORD_WEEK).length() + 2;
        } else if (getTypeSms() == TypeSms.MONTHLY) {
            charCount += conf.getValueForKey(Config.KEYWORD_MONTH).length() + 2;
        }

        Sms sms = new Sms(smsTemplate);
        sms.mListConstraint = listConstraint;
        List<Parser.ConfigField> additionnalFields = new ArrayList<Parser.ConfigField>();

        for (Parser.ConfigField pair : listData) {
            charCount += (Parser.SEPARATOR_COMMA).length() + pair.GetFieldLength();
            if (charCount < maxChar) {
                addField(pair, additionnalFields);
            } else {
                // Create the first sms and add it to sms list
                configureDraftSmsWithDefaultFields(sms);
                sms.mListData = additionnalFields;
                sms.mStatus = Status.DRAFT;
                sms.mType = getTypeSms();
                list.add(sms);
                // Reinit field for a new sms
                sms = new Sms(smsTemplate);
                additionnalFields = new ArrayList<Parser.ConfigField>();
                charCount = baseCharCount;
                charCount += (Parser.SEPARATOR_COMMA).length() + pair.GetFieldLength();
                addField(pair, additionnalFields);
                sms.mListConstraint = listConstraint;
            }
        }
        // Add the last sms
        configureDraftSmsWithDefaultFields(sms);
        sms.mListData = additionnalFields;
        sms.mStatus = Status.DRAFT;
        sms.mType = getTypeSms();
        list.add(sms);
    }

    /**
     * This function add a ConfigField in a list
     *
     * @param field field to add
     * @param list  list where the field will be added
     */
    protected void addField(Parser.ConfigField field, List<Parser.ConfigField> list) {
        String second = null;
        if (field.Type.equals(TypeData.TEXT.toString())) {
            second = "";
        } else if (field.Type.equals(TypeData.NUMBER.toString()) || field.Type.isEmpty()) {
            second = String.valueOf(Integer.MIN_VALUE);
        }
        list.add(new Parser.ConfigField(field.Name, second));
    }

    /**
     * Create a draft sms from a specific sms
     *
     * @param sms sms to configure the draft from
     */
    protected abstract void configureDraftSmsWithDefaultFields(final Sms sms);


    /**
     * set The alreadyset boolean to the param value and show a warning toast
     *
     * @param mAlreadySent
     */
    public void setmAlreadySent(boolean mAlreadySent, ArrayList<Sms> list) {
        if (mAlreadySent != this.mAlreadySent) {
            if (mAlreadySent) {
                getLoaderManager().destroyLoader(HelperReportSent.REPORT_IS_SENT_LOADER);
                Toast.makeText(mContext, getString(R.string.warning_already_sent), Toast.LENGTH_LONG).show();
            }
            this.mAlreadySent = mAlreadySent;
        }

        this.mAlreadySentList = list;
    }
}
