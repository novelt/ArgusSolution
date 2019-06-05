package org.argus.sms.app.fragment;

import android.app.Activity;
import android.app.AlarmManager;
import android.app.AlertDialog;
import android.app.PendingIntent;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import com.squareup.otto.Subscribe;

import java.util.Date;

import butterknife.ButterKnife;
import butterknife.InjectView;
import fr.openium.androkit.utils.ToastUtils;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.ConfigTest;
import org.argus.sms.app.R;
import org.argus.sms.app.activity.ActivityDashboard;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.EventConfigSmsCount;
import org.argus.sms.app.utils.EventSyncFinished;
import org.argus.sms.app.utils.EventSyncMessageReceivedCount;
import org.argus.sms.app.utils.HelperPreference;
import org.argus.sms.app.utils.HelperSmsSender;
import org.argus.sms.app.utils.HelperSync;
import org.argus.sms.app.utils.OttoSingleton;

/**
 * Synchronisation fragment
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentSynchronization extends AbstractFragment implements View.OnClickListener {

    private final static String TAG = FragmentSynchronization.class.getSimpleName();
    private final static boolean DEBUG = true;


    @InjectView(R.id.fragment_synchronisation_Button_sync)
    protected Button mButtonSync;
    @InjectView(R.id.fake_synch_button)
    protected Button mButtonFalseSync;
    @InjectView(R.id.fragment_synchronisation_TextView_lastsync)
    protected TextView mTextViewLastSync;
    @InjectView(R.id.fragment_synchronisation_ProgressBar)
    protected ProgressBar mProgressSync;
    @InjectView(R.id.fragment_synchronisation_TextView_sync_in_progress_since)
    protected TextView mTextViewSyncInProgress;
    private Toast mToast;


    /**
     * Interface for the Synchronisation Fragment host
     */
    public interface IFragmentSynchronizationListener {
        public void onSynchronisationFinished();
    }

    private IFragmentSynchronizationListener mListener;

    /**
     * Create a new instance of the Fragment Synchronization
     * @return the new instance
     */
    public static FragmentSynchronization newInstance() {
        FragmentSynchronization fragment = new FragmentSynchronization();
        return fragment;
    }

    public FragmentSynchronization() {
        // Required empty public constructor
    }

    @Override
    public void onAttach(Activity activity) {
        super.onAttach(activity);
        try {
            mListener = (IFragmentSynchronizationListener) activity;
        } catch (ClassCastException e) {
            throw new ClassCastException(activity.toString()
                    + " must implement OnFragmentInteractionListener");
        }
    }


    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        OttoSingleton.getInstance().getBus().register(this);

        setHasOptionsMenu(true);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View main = inflater.inflate(R.layout.fragment_synchronisation, container, false);
        ButterKnife.inject(this, main);
        mButtonSync.setOnClickListener(this);
        mButtonFalseSync.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                fakeSynch();
            }
        });
        configureDisplay();
        return main;
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        OttoSingleton.getInstance().getBus().unregister(this);
    }


    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    /**
     * Configure the display of the sync screen
     */
    private void configureDisplay() {
       configureDisplay(HelperSms.getSmsConfigAndModelCount(mContext));
    }

    /**
     * Configure the display of the sync screen for a specific count of sms
     * @param smsCount number of sms
     */
    private void configureDisplay(int smsCount) {
        if (HelperPreference.isSyncRunningForMoreThanOneHour(mContext)){
            HelperPreference.saveSyncStartDate(mContext,0);
        }
        if (HelperPreference.isSyncInProgress(mContext)) {
            mButtonSync.setVisibility(View.GONE);
            mButtonFalseSync.setVisibility(View.GONE);
            mTextViewLastSync.setVisibility(View.GONE);
            mProgressSync.setVisibility(View.VISIBLE);
            mTextViewSyncInProgress.setVisibility(View.VISIBLE);
            mTextViewSyncInProgress.setText(HelperSync.getSyncInProgressText(mContext,smsCount));
        } else {
            long timestamp = HelperPreference.getlastSync(mContext);
            if (timestamp == 0) {
                mTextViewLastSync.setVisibility(View.GONE);
            } else {
                mTextViewLastSync.setVisibility(View.VISIBLE);
                mTextViewLastSync.setText(HelperSync.getLastSyncText(mContext));
            }
            mButtonSync.setVisibility(View.VISIBLE);
            mProgressSync.setVisibility(View.GONE);
            mTextViewSyncInProgress.setVisibility(View.GONE);

            if (!Config.IsTest)
                mButtonFalseSync.setVisibility(View.GONE);
            else
                mButtonFalseSync.setVisibility(View.VISIBLE);
        }

        // Don't display Fake synchronization is others Mode than DEBUG
        if (!BuildConfig.DEBUG){
            mButtonFalseSync.setVisibility(View.GONE);
        }
    }

    @Override
    public void onClick(final View view) {
        if (view == mButtonSync) {
            onButtonPressed();
        }
    }


    /**
     * Handle the click on the sync button
     */
    public void onButtonPressed() {

            if (HelperPreference.isAServerIsConfigured(mContext)) {
                this.mButtonSync.setEnabled(false); // avoid clicking multiple times on this button and ask for multiple sync
                HelperPreference.saveConfigSmsCount(mContext, 1000);
                HelperSms.sendSyncRequest(mContext, new HelperSmsSender.SmsListener<Sms>() {
                    @Override
                    public void onSmsSentSuccess(final String tag, final Sms sms) {
                        configureDisplay();
                    }

                    @Override
                    public void onSmsSentError(final String tag, final Sms sms, final int error) {
                        System.out.println(tag);
                        mButtonSync.setEnabled(true);
                        // Nothing to do, the message is displayed by the above listener
                    }
                });
            } else {
                mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, mContext, R.string.aucun_serveur_configure, Toast.LENGTH_LONG);
            }
    }

    /**
     * Used in test mode to fake the synch process
     */
    private void fakeSynch() {
        String id = HelperPreference.getSmsIdAndInc(mContext);
        HelperPreference.saveWaitingSyncId(mContext, Integer.parseInt(id));
        HelperPreference.saveSyncStartDate(mContext, new Date().getTime());
        HelperPreference.saveLastSyncCount(mContext, 0);
        for (String sms : ConfigTest.SYNCH_SMS) {

            String message = ConfigTest.ANDROIDID + HelperPreference.getWaitingSyncId(mContext) + " " + sms;
            HelperSms.parseSmsIfConfig(mContext, message);
            HelperSms.saveSmsToDatabase(mContext, message);

            // increase the synch sms receive count
            int count = HelperPreference.getlastSyncCount(mContext);
            count++;
            HelperPreference.saveLastSyncCount(mContext, count);

            OttoSingleton.getInstance().getBus().post(new EventSyncMessageReceivedCount(count));
            int finalCount = HelperPreference.getConfigSmsCount(mContext);
            if (count == finalCount) { // I see no reason for that yet... || count + 1 == HelperPreference.getConfigSmsCount(context)) {
                if (BuildConfig.DEBUG && DEBUG) {
                    Log.d(TAG, "onReceive sync complete");
                }
                // delete old config
                String selection = HelperSms.getSmsConfigAndModelCountSelection(false);
                String[] selectionArgs = HelperSms.getSmsConfigAndModelCountSelectionArgs(mContext);
                mContext.getContentResolver().delete(SesContract.Sms.CONTENT_URI, selection, selectionArgs);
                HelperPreference.saveLastSync(mContext, new Date().getTime());
                int synchId =  HelperPreference.getWaitingSyncId(mContext);
                HelperPreference.saveLastSyncId(mContext, synchId);
                HelperPreference.clearCurrentSyncData(mContext);
                OttoSingleton.getInstance().getBus().post(new EventSyncFinished());
            }
        }
    }

    /**
     * Event sent by the Otto bus when the count of sms config is known
     * @param event event received
     */
    @Subscribe
    public void onEventConfigSmsCount(EventConfigSmsCount event){
        if (BuildConfig.DEBUG && DEBUG){
            Log.d(TAG, "onEventConfigSmsCount event="+event.getSmsCount());
        }
        configureDisplay();
    }

    /**
     * Event sent by the Otto bus each time a new config sms is received
     * @param event event received
     */
    @Subscribe
    public void onEventSyncMessageReceivedCount(EventSyncMessageReceivedCount event){
        if (BuildConfig.DEBUG && DEBUG){
            Log.d(TAG, "onEventConfigSmsCount event="+event.getSmsCount());
        }
        configureDisplay(event.getSmsCount());
    }

    /**
     * Event sent by the Otto bus when the config is finished
     * @param event event received
     */
    @Subscribe
    public void onEventSyncFinished(EventSyncFinished event){
        new AlertDialog.Builder(getActivity())
                .setTitle(getString(R.string.information))
                .setMessage(getString(R.string.sync_restart))
                .setPositiveButton(android.R.string.yes, new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int which) {
                        Intent i = getActivity().getBaseContext().getPackageManager()
                                .getLaunchIntentForPackage(getActivity().getBaseContext().getPackageName() );
                        AlarmManager mgr = (AlarmManager)getActivity().getSystemService(Context.ALARM_SERVICE);
                        mgr.set(AlarmManager.RTC, System.currentTimeMillis() + 1000, PendingIntent.getActivity(getActivity(), 0, i, PendingIntent.FLAG_ONE_SHOT));
                        Intent intent = new Intent(getActivity(), ActivityDashboard.class);
                        intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
                        intent.putExtra("Restart", true);
                        startActivity(intent);
                        getActivity().finish();
                    }
                })

                .setIcon(android.R.drawable.ic_dialog_alert)
                .show();
    }

}
