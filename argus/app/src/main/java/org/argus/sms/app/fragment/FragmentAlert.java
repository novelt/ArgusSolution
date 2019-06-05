package org.argus.sms.app.fragment;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.database.Cursor;
import android.graphics.Color;
import android.os.Bundle;
import android.support.v4.content.Loader;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import butterknife.ButterKnife;
import butterknife.InjectView;
import fr.openium.androkit.utils.ToastUtils;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.model.TypeData;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperConstraint;
import org.argus.sms.app.utils.HelperPreference;
import org.argus.sms.app.view.IReportItems;
import org.argus.sms.app.view.IReportItemsChangeListener;
import org.argus.sms.app.view.ScrollViewWithScrollListener;
import org.argus.sms.app.view.ViewReportDate;
import org.argus.sms.app.view.ViewReportNumber;
import org.argus.sms.app.view.ViewReportText;

/**
 * Alert Fragment use to display the Alert screen
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentAlert extends AbstractFragmentSend implements ScrollViewWithScrollListener.IScrollListener, IReportItemsChangeListener, View.OnClickListener {
    private final static String TAG = FragmentAlert.class.getSimpleName();
    private final static boolean DEBUG = true;
    private final static String  EMPTY_FIELD = "";

    private static final short ALPHA_MAX = 130;
    private static final float COLOR_MAX = 255.f;

    private static float ALPHA_MAX_FLOAT;
    @InjectView(org.argus.sms.app.R.id.fragment_alert_LinearLayout_items)
    protected LinearLayout mLinearLayout;
    @InjectView(org.argus.sms.app.R.id.fragment_alert_TextView_smsCount)
    protected TextView mTextViewSmsCount;
    @InjectView(org.argus.sms.app.R.id.fragment_alert_ScrollView)
    protected ScrollViewWithScrollListener mScrollView;
    @InjectView(org.argus.sms.app.R.id.fragment_alert_Button_send)
    protected ImageButton mButtonSend;


    {
        ALPHA_MAX_FLOAT = Math.round(ALPHA_MAX);
    }

    private boolean mLimitReached;
    private int mTextViewColor;
    private int mInitialCount;
    private int mMaxSmsCount;

    /**
     * Interface to communicate with the Fragment
     */
    public interface IFragmentAlertListener {
        public void onAlertSent();
    }

    private IFragmentAlertListener mListener;

    /**
     * Create a new instance of FragmentAlert
     * @return
     */
    public static FragmentAlert newInstance() {
        FragmentAlert fragment = new FragmentAlert();
        return fragment;
    }

    public FragmentAlert() {
        // Required empty public constructor
    }

    @Override
    public void onAttach(Activity activity) {
        super.onAttach(activity);
        try {
            mListener = (IFragmentAlertListener) activity;
        } catch (ClassCastException e) {
            throw new ClassCastException(activity.toString()
                    + " must implement OnFragmentInteractionListener");
        }
    }


    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);
        loadConfigData();
        mMaxSmsCount = HelperPreference.getConfigSmsCharCount(mContext);
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        mTextViewColor = getResources().getColor(org.argus.sms.app.R.color.oms_grey);
        View rootView = inflater.inflate(org.argus.sms.app.R.layout.fragment_alert, container, false);
        ButterKnife.inject(this, rootView);
        mButtonSend.setOnClickListener(this);
        mScrollView.setScrollListener(this);
        return rootView;
    }

    @Override
    public void onDestroyView() {
        mScrollView.setScrollListener(null);
        super.onDestroyView();
    }

    @Override
    public void onStop() {
        super.onStop();
        hideKeyboard();
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    @Override
    public void onClick(final View view) {
        if (view == mButtonSend){
            onSendClicked();
        }
    }

    @Override
    public void onSendClicked() {
        if (mLimitReached) {
            mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, mContext, org.argus.sms.app.R.string.limit_reached, Toast.LENGTH_LONG, Gravity.CENTER);
        } else {
            if (isReportsItemsValid()) {
                DialogInterface.OnClickListener listener = new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialogInterface, int i) {
                        getView().post(new Runnable() {
                            @Override
                            public void run() {
                                if (!HelperPreference.getNetworkLock(getContext()))
                                    displayConfirm();
                            }
                        });
                    }
                };

                if (! checkConnectivityToSendSms(true,listener )) {
                    getView().post(new Runnable() {
                        @Override
                        public void run() {
                            displayConfirm();
                        }
                    });
                }

            } else {
                mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, mContext, org.argus.sms.app.R.string.missing_fields, Toast.LENGTH_LONG,Gravity.CENTER);
            }
        }

    }

    /**
     * Show a confirmation dialog box before sending an Alert.
     */
    private void displayConfirm() {
        final Sms sms = buildSms();
        String title = getString(org.argus.sms.app.R.string.send_report);
        String message = sms.toConfirmDialog(getString(org.argus.sms.app.R.string.alert));
        if (Config.IsTest)
            message = getString(org.argus.sms.app.R.string.warning_test_mode) + "\n" + message;

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
                sendAndSaveSms(sms);
            }
        });

        dialog.show();


        /*OKDialogFragmentMessage dialog = OKDialogFragmentMessage.newInstance(title,message,okText,cancelText,true);

        dialog.setListener(new OKDialogFragmentMessage.OKDialogMessageListener() {
            @Override
            public void onOkClicked(final DialogFragment dialogFragment) {
                sendAndSaveSms(sms);
            }

            @Override
            public void onCancelClicked(final DialogFragment dialogFragment) {
                Toast.makeText(mContext, getString(R.string.sms_not_sent), Toast.LENGTH_SHORT).show();
            }
        });
        dialog.show(getFragmentManager(),"dialog");*/
    }

    /**
     * Build a new Sms alert from the current id, type, date and the alert items
     * @return newly created sms
     */
    private Sms buildSms() {
        Sms sms = new Sms();
        sms.mId = Integer.parseInt(HelperPreference.getSmsIdAndInc(mContext));
        sms.mType = getTypeSms();
        sms.mTimestamp = new Date().getTime();
        sms.mListData = new ArrayList<Parser.ConfigField>();
        sms.mStatus = Status.SENT;
        for (IReportItems item : mListReportsItems) {
            if (item.isEmpty()) // Set default value "N/C" if empty
                sms.mListData.add(new Parser.ConfigField(item.getKey(), EMPTY_FIELD));
            else
                sms.mListData.add(new Parser.ConfigField(item.getKey(), item.getValue()));
        }
        return sms;
    }


    @Override
    protected TypeSms getTypeSms() {
        return TypeSms.ALERT;
    }

    @Override
    protected int getSendSuccessMessage() {
        return org.argus.sms.app.R.string.sent_alert;
    }

    @Override
    protected int getSendingMessage() {
        return org.argus.sms.app.R.string.sending_alert;
    }

    @Override
    protected SubTypeSms getSubType() {
        return SubTypeSms.MODEL_ALERT;
    }

    /**
     * Called when the database request return a result
     * Parse the result send from the database request
     * @param cursorLoader the id of the request
     * @param cursor the data
     */
    @Override
    public void onLoadFinished(final Loader<Cursor> cursorLoader, final Cursor cursor) {
        if (cursor != null && cursor.moveToFirst()) {

            checkConnectivityToSendSms(false, null);

            String smsText = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));
            List<Parser.Constraint> listConstraint = Parser.getConstraintsFromSmsReport(smsText, Config.getInstance(mContext));
            List<Parser.ConfigField> listData = Parser.getOtherFieldsFromSms(smsText, Config.getInstance(mContext));
            addAlertItemsToScreen(listData, listConstraint);
            mInitialCount = getCharCount();
            displayCharCount();
            getLoaderManager().destroyLoader(LOADER_CONFIG);
        }
    }

    /**
     * Add the alert items on the screen
     * @param items items to add
     */
    private void addAlertItemsToScreen(List<Parser.ConfigField> items, List<Parser.Constraint> constraints) {
        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        for (Parser.ConfigField cf : items) {
            View v = null;

            if (cf.Type.equals(TypeData.TEXT.toString())) {
                v = new ViewReportText(getActivity(), cf.Name, cf.IsOptionnal);
            } else if (cf.Type.equals(TypeData.NUMBER.toString())) {
                v = new ViewReportNumber(getActivity(),getActivity(), cf.Name, cf.IsOptionnal);
            } else {
                v = new ViewReportDate(getActivity(), getActivity().getSupportFragmentManager(), cf.Name, cf.IsOptionnal);
            }
            if (v != null) {
                IReportItems item = (IReportItems) v;
                item.setOnValueChangeListener(this);
                mLinearLayout.addView(v, lp);
                mListReportsItems.add(item);
            }
        }

        if (constraints != null) {
            for (Parser.Constraint pair : constraints) {
                IReportItems itemOne = getConstraintItem(pair.FieldFrom.trim());
                IReportItems itemTwo = getConstraintItem(pair.FieldTo.trim());
                if (itemOne != null && itemTwo != null && itemOne instanceof ViewReportNumber && itemTwo instanceof  ViewReportNumber) {
                    try {
                        ViewReportNumber viewOne = (ViewReportNumber) itemOne;
                        ViewReportNumber viewTwo = (ViewReportNumber) itemTwo;

                        HelperConstraint.AddConstraintInViewNumber(viewOne, viewTwo, pair.Constraint);
                        }
                    catch(Exception ex)
                    {
                        Log.e(TAG, "Impossible to add constraints on field with type != Integer");
                    }

                }
            }
        }
    }

    /**
     * get the ViewReportNumber with title beginning with begins
     * @param begins first character of title of the ViewReportNumber
     * @return the viewReportNumber found or null
     */
    private IReportItems getConstraintItem(String begins) {
        for (IReportItems reportItem : mListReportsItems)
            if (reportItem.getKey().startsWith(begins))
                return reportItem;
        return null;
    }

    /**
     * Called when the scroll position of the scrollview has changed
     * @param l left
     * @param t top
     * @param oldl old left
     * @param oldt old top
     * @see android.view.ViewTreeObserver.OnScrollChangedListener
     */
    @Override
    public void onScrollChanged(final int l, final int t, final int oldl, final int oldt) {
        int textViewHeight = mTextViewSmsCount.getHeight();
        int alpha;
        if (t > textViewHeight) {
            alpha = ALPHA_MAX;
            mTextViewColor = Color.WHITE;
        } else {
            float textViewHeightFloat = Float.valueOf(textViewHeight);
            float resultBackgroundAlpha = t * (ALPHA_MAX_FLOAT / textViewHeightFloat);
            alpha = Math.round(resultBackgroundAlpha);
            float resultTextColor = t * ((COLOR_MAX - 144) / textViewHeightFloat);
            int textColorGrey = Math.round(resultTextColor);
            mTextViewColor = Color.argb(255, 144 + textColorGrey, 144 + textColorGrey, 144 + textColorGrey);
            if (BuildConfig.DEBUG && DEBUG) {
                Log.d(TAG, "onScrollChanged t=" + t + " textColorGrey=" + textColorGrey);
            }
        }
        if (BuildConfig.DEBUG && DEBUG) {
            Log.d(TAG, "onScrollChanged t=" + t + " alpha=" + alpha);
        }
        int color = Color.argb(alpha, 128, 128, 128);
        mTextViewSmsCount.setBackgroundColor(color);
        int textColor;
        if (mLimitReached) {
            textColor = Color.RED;
        } else {
            textColor = mTextViewColor;
        }
        mTextViewSmsCount.setTextColor(textColor);
    }

    /**
     * Display the count of char left for the SMS
     */
    public void displayCharCount() {
        int currentLenght = getCharCount();
        if (currentLenght > mMaxSmsCount) {
            mLimitReached = true;
            mTextViewSmsCount.setTextColor(Color.RED);
        } else {
            mLimitReached = false;
            mTextViewSmsCount.setTextColor(mTextViewColor);
        }
        mTextViewSmsCount.setText(getString(org.argus.sms.app.R.string.sms_count, currentLenght - mInitialCount, mMaxSmsCount - mInitialCount));
    }

    /**
     * Get the current char count
     * @return the current char count
     */
    public int getCharCount() {
        Sms sms = buildSms();
        String text = sms.toSms(TypeSms.ALERT, Config.getInstance(mContext), true);
        return text.length();
    }

    @Override
    public void onValueChange() {
        displayCharCount();
    }
}
