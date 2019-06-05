package org.argus.sms.app.fragment;

import android.app.Activity;
import android.content.pm.PackageInfo;
import android.database.Cursor;
import android.graphics.ColorMatrix;
import android.graphics.ColorMatrixColorFilter;
import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.widget.CardView;
import android.text.TextUtils;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.TextView;

import com.squareup.otto.Subscribe;

import butterknife.ButterKnife;
import butterknife.InjectView;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperAlert;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.EventSyncFinished;
import org.argus.sms.app.utils.EventSyncMessageReceivedCount;
import org.argus.sms.app.utils.HelperPreference;
import org.argus.sms.app.utils.HelperReminder;
import org.argus.sms.app.utils.HelperSync;
import org.argus.sms.app.utils.OttoSingleton;

/**
 * Dashboard Fragment
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentDashboard extends AbstractFragment implements View.OnClickListener, LoaderManager.LoaderCallbacks<Cursor> {
    private final static String TAG = FragmentDashboard.class.getSimpleName();
    private final static boolean DEBUG = true;

    private static final int LOADER_DASHBOARD = 12;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_report_weekly)
    protected CardView mButtonReportWeekly;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_Image_Weekly)
    protected ImageView mImageViewReportWeekly;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_TextView_weekly)
    protected TextView mTextViewReportWeekly;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_Separator_weekly)
    protected View mViewSeparatorReportWeekly;

    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_report_monthly)
    protected CardView mButtonReportMonthly;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_Image_Monthly)
    protected ImageView mImageViewReportMonthly;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_TextView_monthly)
    protected TextView mTextViewReportMonthly;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_Separator_monthly)
    protected View mViewSeparatorReportMonthly;

    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_alert)
    protected CardView mButtonAlert;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_Image_alert)
    protected ImageView mImageViewReportAlert;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_TextView_alert)
    protected TextView mTextViewReportAlert;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_Separator_alert)
    protected View mViewSeparatorReportAlert;

    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_history)
    protected CardView mButtonHistory;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_Image_history)
    protected ImageView mImageViewReportHistory;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_TextView_history)
    protected TextView mTextViewReportHistory;
    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_Separator_history)
    protected View mViewSeparatorReportHistory;

    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_version)
    protected TextView mTextViewVersion;

    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_TextView_sync)
    protected TextView mTextViewSync;

    @InjectView(org.argus.sms.app.R.id.fragment_dashboard_Progressbar_sync)
    protected ProgressBar mProgressbarSync;


    private IFragmentDashboardListener mListener;

    /**
     * Interface to listen to the Dashboard events
     */
    public interface IFragmentDashboardListener {
        void startReportWeekly();

        void startReportMonthly();

        void startAlert();

        void startHistory();
    }

    public FragmentDashboard() {
    }

    @Override
    public void onAttach(final Activity activity) {
        super.onAttach(activity);
        mListener = (IFragmentDashboardListener) activity;
    }

    @Override
    public void onCreate(final Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (HelperPreference.getLastSyncId(mContext) != -1) {
            getLoaderManager().initLoader(LOADER_DASHBOARD, null, this);
        }
        HelperReminder.setUpRepeatAlarmCheck(mContext);
        OttoSingleton.getInstance().getBus().register(this);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View rootView = inflater.inflate(org.argus.sms.app.R.layout.fragment_dashboard, container, false);
        ButterKnife.inject(this, rootView);
        mButtonReportWeekly.setOnClickListener(this);
        mButtonReportMonthly.setOnClickListener(this);
        mButtonAlert.setOnClickListener(this);
        mButtonHistory.setOnClickListener(this);
        setHasOptionsMenu(true);
        displayVersion();

        return rootView;
    }

    @Override
    public void onResume() {
        super.onResume();

        activateOrDesactivateButtons();

        displayHomeText();
    }


    @Override
    public void onDestroy() {
        OttoSingleton.getInstance().getBus().unregister(this);
        super.onDestroy();
    }

    /**
     * Display the version of the application
     */
    private void displayVersion() {
        //String version = getString(org.argus.sms.app.R.string.appversion);
        String version;
        try {
            PackageInfo pInfo = mContext.getPackageManager().getPackageInfo(mContext.getPackageName(), 0);
            version = pInfo.versionName;
        } catch (Exception e){
            version = "0";
        }

        mTextViewVersion.setText(version);
    }

    /**
     * Display the message on the home screen.
     * 1. The sync state
     * 2. the reminder
     * 3. the last message
     */
    private void displayHomeText() {
        if (!displaySyncState()) {
            if (!displayReminder()) {
                if (!displayLastMessage()) {
                    mTextViewSync.setVisibility(View.GONE);
                    mProgressbarSync.setVisibility(View.GONE);
                }
            }
        }
    }

    /**
     * Display the sync state if needed
     * @return true if a sync state is displayed, false otherwise
     */
    private boolean displaySyncState() {
        return displaySyncState(HelperSms.getSmsConfigAndModelCount(mContext));
    }

    /**
     * Display the sync state for a specific count
     * @return true if a sync state is displayed, false otherwise
     */
    private boolean displaySyncState(int count) {
        boolean syncInProgress = HelperPreference.isSyncInProgress(mContext);
        mProgressbarSync.setVisibility(syncInProgress ? View.VISIBLE : View.GONE);
        mTextViewSync.setVisibility(syncInProgress ? View.VISIBLE : View.GONE);
        if (syncInProgress) {
            mTextViewSync.setText(HelperSync.getSyncInProgressText(mContext, count));
        }
        return syncInProgress;
    }

    /**
     * Display the reminder if needed
     * @return true if a reminder is displayed, false otherwise
     */
    private boolean displayReminder() {
        boolean displayReminder = false;
        String message = "";

        // If Weekly Template is disable
        if (! TextUtils.isEmpty(Config.getInstance(mContext).getValueForKey(Config.KEYWORD_WEEKLY))){
            message = HelperReminder.getMessageIfReportTextNeededForLastWeek(mContext, mContext.getContentResolver());
        }

        // If we don't have to display Weekly reminder
        if (TextUtils.isEmpty(message)){
            // If Monthly Template is disable
            if (! TextUtils.isEmpty(Config.getInstance(mContext).getValueForKey(Config.KEYWORD_MONTHLY))) {
                message = HelperReminder.getMessageIfReportTextNeededForLastMonth(mContext, mContext.getContentResolver());
            }
        }
        if (!TextUtils.isEmpty(message)) {
            displayReminder = true;
            mProgressbarSync.setVisibility(View.GONE);
            mTextViewSync.setVisibility(View.VISIBLE);
            mTextViewSync.setText(message);
        }
        return displayReminder;
    }

    /**
     * Display the last message if needed
     * @return true if a last message is displayed, false otherwise
     */
    private boolean displayLastMessage() {
        boolean displayLastMessage = false;
        String message = HelperPreference.getLastMessage(mContext);
        if (!TextUtils.isEmpty(message)) {
            displayLastMessage = true;
            mProgressbarSync.setVisibility(View.GONE);
            mTextViewSync.setVisibility(View.VISIBLE);
            mTextViewSync.setText(message);
        }
        return displayLastMessage;
    }


    /**
     * Event received by the Otto Bus when the sync is finished
     * @param event sync finished event
     */
    @Subscribe
    public void onEventSyncFinished(EventSyncFinished event) {
        getLoaderManager().initLoader(LOADER_DASHBOARD, null, this);
        displaySyncState();
    }

    /**
     * Event received by the Otto Bus when the count of sync message has changed
     * @param event
     */
    @Subscribe
    public void onEventSyncMessageReceivedCount(EventSyncMessageReceivedCount event) {
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "onEventConfigSmsCount event=" + event.getSmsCount());
        }
        displaySyncState(event.getSmsCount());
    }


    @Override
    public void onClick(final View view) {
        if (view == mButtonReportWeekly) {
            onClickReportWeekly();
        } else if (view == mButtonReportMonthly) {
            onClickReportMonthly();
        } else if (view == mButtonAlert) {
            onClickAlert();
        } else if (view == mButtonHistory) {
            onClickHistory();
        }
    }

    /**
     * Handle the click on weekly button
     */
    private void onClickReportWeekly() {
        mListener.startReportWeekly();
    }

    /**
     * Handle the click on monthly button
     */
    private void onClickReportMonthly() {
        mListener.startReportMonthly();
    }

    /**
     * Handle the click on alert button
     */
    private void onClickAlert() {
        mListener.startAlert();
    }

    /**
     * Handle the click on history button
     */
    private void onClickHistory() {
        mListener.startHistory();
    }

    @Override
    public Loader<Cursor> onCreateLoader(final int identifiant, final Bundle bundle) {
        String selection = SesContract.Sms.TYPE + "=?";
        String[] selectionArgs = {String.valueOf(TypeSms.MODEL.toInt())};
        CursorLoader loader = new CursorLoader(mContext, SesContract.Sms.CONTENT_URI, null, selection, selectionArgs, null);
        return loader;
    }

    @Override
    public void onLoadFinished(final Loader<Cursor> cursorLoader, final Cursor cursor) {
        if (cursorLoader.getId() == LOADER_DASHBOARD) {
            if (cursor != null && cursor.moveToFirst()) {
                Config.getInstance(mContext).loadDataFromCursor(cursor, mContext);
                activateOrDesactivateButtons();
            }
        }
    }

    /**
     * Activate or disactivate buttons on home screen depending on the configuration state
     */
    private void activateOrDesactivateButtons() {
        if (Config.getInstance(mContext).isConfigLoaded()) {
            configureDashboardEntry(Config.KEYWORD_MONTHLY, org.argus.sms.app.R.string.report_monthly_short, org.argus.sms.app.R.drawable.ic_mois, mButtonReportMonthly, mImageViewReportMonthly, mTextViewReportMonthly, mViewSeparatorReportMonthly);
            configureDashboardEntry(Config.KEYWORD_WEEKLY, org.argus.sms.app.R.string.report_weekly_short, org.argus.sms.app.R.drawable.ic_semaine, mButtonReportWeekly, mImageViewReportWeekly, mTextViewReportWeekly, mViewSeparatorReportWeekly);
            configureDashboardEntry(Config.KEYWORD_ALERT, org.argus.sms.app.R.string.alert, org.argus.sms.app.R.drawable.ic_alerte, mButtonAlert, mImageViewReportAlert, mTextViewReportAlert, mViewSeparatorReportAlert);
            mButtonHistory.setEnabled(true);
            mImageViewReportHistory.setImageResource(org.argus.sms.app.R.drawable.ic_historique);
            mTextViewReportHistory.setVisibility(View.VISIBLE);
            mTextViewReportHistory.setText(getString(org.argus.sms.app.R.string.history));
            mViewSeparatorReportHistory.setVisibility(View.VISIBLE);
        } else {
            mButtonReportWeekly.setEnabled(false);
            mButtonReportMonthly.setEnabled(false);
            mButtonAlert.setEnabled(false);
            mButtonHistory.setEnabled(false);

            mViewSeparatorReportWeekly.setVisibility(View.GONE);
            mViewSeparatorReportMonthly.setVisibility(View.GONE);
            mViewSeparatorReportAlert.setVisibility(View.GONE);
            mViewSeparatorReportHistory.setVisibility(View.GONE);

            mTextViewReportWeekly.setVisibility(View.GONE);
            mTextViewReportMonthly.setVisibility(View.GONE);
            mTextViewReportAlert.setVisibility(View.GONE);
            mTextViewReportHistory.setVisibility(View.GONE);

            mImageViewReportWeekly.setImageDrawable(convertToGrayscale(getResources().getDrawable(org.argus.sms.app.R.drawable.ic_semaine)));
            mImageViewReportMonthly.setImageDrawable(convertToGrayscale(getResources().getDrawable(org.argus.sms.app.R.drawable.ic_mois)));
            mImageViewReportHistory.setImageDrawable(convertToGrayscale(getResources().getDrawable(org.argus.sms.app.R.drawable.ic_historique)));
            mImageViewReportAlert.setImageDrawable(convertToGrayscale(getResources().getDrawable(org.argus.sms.app.R.drawable.ic_alerte)));
        }

        if (HelperPreference.isExternalArgusAlertEnabled(mContext) && HelperAlert.isArgusAlertApplicationInstalled(mContext)) {
            mButtonAlert.setEnabled(true);
            mImageViewReportAlert.setImageDrawable(removeGrayScale(getResources().getDrawable(org.argus.sms.app.R.drawable.ic_alerte)));
            mTextViewReportAlert.setVisibility(View.VISIBLE);
            mTextViewReportAlert.setText(getString(org.argus.sms.app.R.string.alert));
            mViewSeparatorReportAlert.setVisibility(View.VISIBLE);
        }
    }

    /**
     * Configure a dashboard button depending on the state
     * @param configKeyValue key in the config
     * @param drawableResourceId drawable to display
     * @param button button to modify
     * @param imageView imageView to modify
     * @param textview textView to modify
     * @param separator separator to modify
     */
    private void configureDashboardEntry(String configKeyValue, int textResourceId, int drawableResourceId, View button, ImageView imageView, TextView textview, View separator) {
        if (TextUtils.isEmpty(Config.getInstance(mContext).getValueForKey(configKeyValue))) {
            button.setEnabled(false);
            imageView.setImageDrawable(convertToGrayscale(getResources().getDrawable(drawableResourceId)));
            textview.setVisibility(View.GONE);
            separator.setVisibility(View.GONE);
        } else {
            button.setEnabled(true);
            textview.setVisibility(View.VISIBLE);
            separator.setVisibility(View.VISIBLE);
            imageView.setImageResource(drawableResourceId);
            textview.setText(getString(textResourceId));
        }
    }

    /**
     * Change a drawable to it's grayscale value
     *
     * @param drawable drawable to convert to grayscale
     * @return drawable in grayscale
     */
    protected Drawable convertToGrayscale(Drawable drawable)
    {
        return setSaturation(drawable, 0);
    }

    /**
     * Remove the grayscale value to the drawable
     *
     * @param drawable Drawable
     * @return Drawable
     */
    protected Drawable removeGrayScale(Drawable drawable)
    {
        return setSaturation(drawable, 1);
    }

    /**
     * Set the saturation to a drawable
     *
     * @param drawable Drawable
     * @param saturation int
     * @return Drawable
     */
    private Drawable setSaturation(Drawable drawable, int saturation)
    {
        drawable = drawable.mutate();

        ColorMatrix matrix = new ColorMatrix();
        matrix.setSaturation(saturation);

        ColorMatrixColorFilter filter = new ColorMatrixColorFilter(matrix);

        drawable.setColorFilter(filter);

        return drawable;
    }

    @Override
    public void onLoaderReset(final Loader<Cursor> cursorLoader) {

    }
}