package org.argus.sms.app.fragment;

import android.app.Activity;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;

import butterknife.ButterKnife;
import butterknife.InjectView;
import org.argus.sms.app.R;

/**
 * History Fragment
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentPush extends AbstractFragment implements View.OnClickListener {
    private final static String TAG = FragmentPush.class.getSimpleName();
    private final static boolean DEBUG = true;

    private final static String EXTRA_PUSH_TEXT = "EXTRA_PUSH_TEXT";

    private IFragmentPushListener mListener;

    @InjectView(R.id.fragment_push_TextView_alert)
    protected TextView mTextViewAlert;
    @InjectView(R.id.fragment_push_Button_pass)
    protected Button mButtonPass;
    @InjectView(R.id.fragment_push_Button_history)
    protected Button mButtonHistory;
    private String mPushText;



    /**
     * Listener for the Fragment history host
     */
    public interface IFragmentPushListener {
        void onHistoryClicked();
        void onSkipClicked();
    }

    /**
     * Create a new instance of FragmentHistory
     * @return a new FragementHistory
     */
    public static FragmentPush newInstance(String text) {
        FragmentPush fragment = new FragmentPush();
        Bundle b = new Bundle();
        b.putString(EXTRA_PUSH_TEXT,text);
        fragment.setArguments(b);
        return fragment;
    }

    /**
     * Mandatory empty constructor for the fragment manager to instantiate the
     * fragment (e.g. upon screen orientation changes).
     */
    public FragmentPush() {
    }



    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        restoreArgs();
    }

    private void restoreArgs() {
        Bundle arguments = getArguments();
        mPushText = arguments.getString(EXTRA_PUSH_TEXT);
    }


    @Override
    public void onAttach(Activity activity) {
        super.onAttach(activity);
        mContext = activity.getApplicationContext();
        try {
            mListener = (IFragmentPushListener) activity;
        } catch (ClassCastException e) {
            throw new ClassCastException(activity.toString()
                    + " must implement OnFragmentInteractionListener");
        }
    }

    @Override
    public View onCreateView(final LayoutInflater inflater, final ViewGroup container, final Bundle savedInstanceState) {
        View rootView =  inflater.inflate(R.layout.fragment_push, container, false);
        ButterKnife.inject(this, rootView);

        mButtonHistory.setOnClickListener(this);
        mButtonPass.setOnClickListener(this);
        return rootView;
    }

    @Override
    public void onViewCreated(final View view, final Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        mTextViewAlert.setText(mPushText);
    }



    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    @Override
    public void onClick(final View v) {
    if (v == mButtonHistory){
        onClickHistory();
    }else if (v == mButtonPass){
        onClickPass();
    }
    }

    private void onClickPass() {
        if (mListener != null) {
            mListener.onSkipClicked();
        }
    }

    private void onClickHistory() {
        if (mListener != null) {
            mListener.onHistoryClicked();
        }
    }



}
