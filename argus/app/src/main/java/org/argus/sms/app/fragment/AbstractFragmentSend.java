package org.argus.sms.app.fragment;

import android.app.Activity;
import android.content.Context;
import android.database.Cursor;
import android.net.Uri;
import android.os.Bundle;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.util.Log;
import android.view.Gravity;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.widget.Toast;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import fr.openium.androkit.utils.ToastUtils;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperReminder;
import org.argus.sms.app.utils.HelperSmsSender;
import org.argus.sms.app.view.IReportItems;
import org.argus.sms.app.view.OKDialogProgress;

/**
 * Abstract fragment for all fragments with a send button
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public abstract class AbstractFragmentSend extends AbstractFragment implements LoaderManager.LoaderCallbacks<Cursor> {
    private final static String     TAG = AbstractFragmentSend.class.getSimpleName();
    private final static boolean    DEBUG = true;

    protected Context               mContext;
    protected Toast                 mToast;
    protected List<IReportItems>    mListReportsItems;


    protected static final int LOADER_CONFIG = 1;
    protected static final int LOADER_DRAFT = 2;
    protected static final int LOADER_SEND = 3;
    protected static final int LOADER_CONFIRM = 4;
    protected static final int LOADER_CLEAN = 5;
    private int mSendingMessage;

    public AbstractFragmentSend(){
        mListReportsItems = new ArrayList<IReportItems>();
    }


    @Override
    public void onAttach(final Activity activity) {
        super.onAttach(activity);
        mContext = activity.getApplicationContext();
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);
//       mContext.getContentResolver().delete(SesContract.Sms.CONTENT_URI,getSelectionDraft(),getSelectionArgsDraft());
    }

    /**
     * Start the loader for config data
     */
    protected void loadConfigData() {
        getLoaderManager().restartLoader(LOADER_CONFIG, null, this);
    }

    /**
     * Start the loader for draft data
     */
    protected void loadConfigDraft() {
        getLoaderManager().restartLoader(LOADER_DRAFT, null, this);
    }

    /**
     * Start the loader for clean data
     */
    public void loadConfigClean() {
        getLoaderManager().restartLoader(LOADER_CLEAN, null, this);
    }
    /**
     * Start the loader for sending data
     */
    protected void loadSend() {
        getLoaderManager().restartLoader(LOADER_SEND, null, this);
    }

    @Override
    public void onCreateOptionsMenu(final Menu menu, final MenuInflater inflater) {
        inflater.inflate(org.argus.sms.app.R.menu.menu_fragment_send, menu);
    }

    // Never called on Nexus 4.

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == org.argus.sms.app.R.id.action_send) {
            onSendClicked();
            return true;
        } else if (id == android.R.id.home){
            hideKeyboard();
        }

        return super.onOptionsItemSelected(item);
    }

    /**
     * Called when on send is clicked
     */
    public abstract void onSendClicked();

    /**
     * Get the type of sms
     * @return Type of sms
     */
    protected abstract TypeSms getTypeSms();

    /**
     * Send the sms and save it in database
     *
     * @param sms sms to send and save
     */
    protected void sendAndSaveSms(final Sms sms)
    {
        final OKDialogProgress dialog = OKDialogProgress.newInstance(getString(getSendingMessage()),true);
        dialog.show(getFragmentManager(), "progress");
        HelperSmsSender.sendSms(mContext, getTypeSms(), sms, new HelperSmsSender.SmsListener<Sms>() {

            @Override
            public void onSmsSentSuccess(final String tag, final Sms sms) {
                sms.mSendDate = new Date().getTime();
                Uri uri = getActivity().getContentResolver().insert(SesContract.Sms.CONTENT_URI, sms.getContentValues(Config.getInstance(mContext)));
                HelperReminder.setUpReminderNotConfirmed(mContext, getTypeSms(), uri);
                hideKeyboard();
                dialog.dismiss();
                displayOkAndExit();
            }

            @Override
            public void onSmsSentError(final String tag, final Sms sms, final int error) {
                if (BuildConfig.ERROR) {
                    Log.e(TAG, "sendAndSaveSms send impossible");
                }
                dialog.dismiss();
                HelperSmsSender.displayStandardErrorText(mContext);
            }
        }, "message");
    }

    /**
     * Display the OK message and exit
     */
    protected void displayOkAndExit() {
        getActivity().finish();
        ToastUtils.displayToastAndCancelOldIfExists(mToast, mContext, getSendSuccessMessage(), Toast.LENGTH_LONG, Gravity.CENTER);
    }

    /**
     * Display the KO message and exit
     */
    protected void displayNotOkAndExit() {
        getActivity().finish();
        ToastUtils.displayToastAndCancelOldIfExists(mToast, mContext, R.string.sent_report_errors, Toast.LENGTH_LONG, Gravity.CENTER);
    }

    /**
     * Get the sending message for a sending sms
     * @return resource id to the text
     */
    protected abstract int getSendingMessage();

    /**
     * Get the send message for a successful send
     * @return resource id to the text
     */
    protected abstract int getSendSuccessMessage();

    /**
     * Check if all items are valid
     * @return true if valid, false otherwise
     */
    protected boolean isReportsItemsValid() {
        boolean isValid = true;
        for (IReportItems i : mListReportsItems){
            if (!i.isValid()){
                i.isWrong();
                isValid = false;
            }
        }
        return isValid;
    }

    /**
     * create cursor loader(query) to get data from database
     * @param id
     * @param bundle
     * @return
     */
    @Override
    public Loader<Cursor> onCreateLoader(final int id, final Bundle bundle) {

        if (BuildConfig.DEBUG && DEBUG){
            Log.d(TAG,"onCreateLoader id="+id);
        }
        CursorLoader loader = null;
        if (id == LOADER_CONFIG) {
            String selection = SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.SUBTYPE + "=?";
            String[] selectionArgs = {String.valueOf(TypeSms.MODEL.toInt()), String.valueOf(getSubType().toInt())};
            loader = new CursorLoader(mContext, SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, null);
        }else if (id == LOADER_DRAFT || id == LOADER_SEND || id == LOADER_CONFIRM || id == LOADER_CLEAN){
            String selection = getSelectionDraft();
            String[] selectionArgs = getSelectionArgsDraft();
            loader = new CursorLoader(mContext, SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, SesContract.Sms.DISEASE + " ASC");
        }
        return loader;
    }

    /**
     * Get the database selection for draft
     * @return Selection in database
     */
    protected String getSelectionDraft() {
        return SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.STATUS + "=?";
    }

    /**
     * Get the database selection args for draft
     * @return Selection args in database
     */
    protected String[] getSelectionArgsDraft() {
        return new String[]{String.valueOf(getTypeSms().toInt()), String.valueOf(Status.DRAFT.toInt())};
    }

    /**
     * Get the current sub type
     * @return current subtype
     */
    protected abstract SubTypeSms getSubType();

    @Override
    public void onLoaderReset(final Loader<Cursor> cursorLoader) {

    }
    protected void hideKeyboard() {
        for(IReportItems vrn : mListReportsItems){
            vrn.hideKeyboard();
        }
    }
}
