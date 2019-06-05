package org.argus.sms.app.view;

import android.annotation.TargetApi;
import android.app.Activity;
import android.content.Context;
import android.os.Build;
import android.util.AttributeSet;
import android.view.View;
import android.view.inputmethod.InputMethodManager;
import android.widget.LinearLayout;

/**
 * Abstract view for reports
 * Created by Olivier Goutet.
 * Openium 2014
 */
public abstract class AbstractViewReport extends LinearLayout implements IReportItems {

    private IReportItemsChangeListener mListener;
    private Activity mActivity;

    public AbstractViewReport(final Context context) {
        super(context);
        init(context);
    }

    public AbstractViewReport(final Context context, final AttributeSet attrs) {
        super(context, attrs);
        init(context);
    }

    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public AbstractViewReport(final Context context, final AttributeSet attrs, final int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        init(context);
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public AbstractViewReport(final Context context, final AttributeSet attrs, final int defStyleAttr, final int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
        init(context);
    }

    private void init(final Context context) {
        if (context instanceof Activity) {
            mActivity = (Activity) context;
        }
    }

    @Override
    public void setOnValueChangeListener(final IReportItemsChangeListener listener) {
        mListener = listener;
    }

    /**
     * Called when the value has changed
     */
    protected void onContentChanged(){
        if(mListener != null) {
            mListener.onValueChange();
        }
    }

    /**
     * Method to hide the keyboard for the view
     */
    public void hideKeyboard() {
        // check if no view has focus:
        if (mActivity != null) {
            InputMethodManager inputManager = (InputMethodManager) mActivity.getSystemService(Context.INPUT_METHOD_SERVICE);
            View view = mActivity.getCurrentFocus();
            if (view != null) {
                inputManager.hideSoftInputFromWindow(view.getWindowToken(), InputMethodManager.HIDE_NOT_ALWAYS);
            }
        }
    }

}
