package org.argus.sms.app.view;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.support.v7.widget.CardView;
import android.text.Editable;
import android.text.Html;
import android.text.TextUtils;
import android.text.TextWatcher;
import android.util.AttributeSet;
import android.util.Pair;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.inputmethod.InputMethodManager;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import org.argus.sms.app.R;
import org.argus.sms.app.utils.HelperConstraint;

import java.util.ArrayList;

import fr.openium.androkit.utils.ToastUtils;

/**
 * Custom view for a report number
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ViewReportNumber extends AbstractViewReport implements View.OnClickListener, TextWatcher {

    public static final int NO_NUMBER = Integer.MIN_VALUE;
    public static final int MAX_VALUE = 9999;
    private CardView mCardViewMinus;
    private CardView mCardViewPlus;
    private EditText mEditTextCount;
    private TextView mTextViewTitle;

    private int mCount;
    private String mTitle;
    private Activity mActivity;
    private Context mContext;

    private static Toast mToast;

    private boolean     mIsOptionnal = false;

    // Variables used to Constraint management.
    private ArrayList<Pair<HelperConstraint.Constraint, ViewReportNumber>> mConstraints = null;

    public ViewReportNumber(final Context context) {
        super(context);
        init(context, null, null, NO_NUMBER);
    }

    public ViewReportNumber(final Context context, final AttributeSet attrs) {
        super(context, attrs);
        init(context, null, null, NO_NUMBER);
    }

    public ViewReportNumber(final Context context, final Activity activity, final String title, boolean isOpt) {
        super(context);
        init(context, activity, title, NO_NUMBER);
        mIsOptionnal = isOpt;
    }

    public ViewReportNumber(final Context context, final Activity activity, final String title, final int initCount) {
        super(context);
        init(context, activity, title, initCount);
    }

    private void init(final Context context, final Activity activity, String title, int initCount) {
        mActivity = activity;
        mContext = context;
        LayoutInflater li = LayoutInflater.from(context);
        li.inflate(org.argus.sms.app.R.layout.report_number, this, true);
        mTextViewTitle = (TextView) findViewById(org.argus.sms.app.R.id.report_number_TextView_text);
        mCardViewMinus = (CardView) findViewById(org.argus.sms.app.R.id.report_number_CardView_minus);
        mCardViewPlus = (CardView) findViewById(org.argus.sms.app.R.id.report_number_CardView_plus);
        mEditTextCount = (EditText) findViewById(org.argus.sms.app.R.id.report_number_EditText_count);
        mCardViewMinus.setOnClickListener(this);
        mCardViewPlus.setOnClickListener(this);
        mTitle = title;
        mTextViewTitle.setText(title);
        mCount = initCount;
        displayCount(initCount);
        setOnClickListener(this);
        mEditTextCount.addTextChangedListener(this);
    }

    @Override
    public void onClick(final View view) {
        hideKeyboard();
        if (mCardViewMinus == view) {
            onMinusClicked();
        } else if (mCardViewPlus == view) {
            onPlusClicked();
        }
    }

    private void showKeyboard() {
        mEditTextCount.requestFocus();
        InputMethodManager inputMethodManager=(InputMethodManager)mActivity.getSystemService(Context.INPUT_METHOD_SERVICE);
        inputMethodManager.showSoftInput(mEditTextCount, InputMethodManager.SHOW_FORCED);
    }

    @Override
    public void hideKeyboard() {
        super.hideKeyboard();
        InputMethodManager inputManager = (InputMethodManager) mActivity.getSystemService(Context.INPUT_METHOD_SERVICE);
        inputManager.hideSoftInputFromWindow(mEditTextCount.getWindowToken(), 0);
    }

    /**
     * Handle the click on minus
     */
    private void onMinusClicked() {
        showKeyboard();
        mEditTextCount.requestFocus();
        if (mCount == NO_NUMBER) {
            mCount = 0;
            displayCount(mCount);
        } else if (mCount > 0) {
            countChanged(String.valueOf(mCount - 1));
            displayCount(mCount);
        }
        onContentChanged();
    }

    /**
     * Handle the click on plus
     */
    private void onPlusClicked() {
        showKeyboard();

        if (mCount == NO_NUMBER) {
            mCount = 0;
            displayCount(mCount);
        } else if (mCount != MAX_VALUE){
            countChanged(String.valueOf(mCount + 1));
            displayCount(mCount);
        }
    }

    /**
     * Display the count
     * @param count count to display
     */
    private void displayCount(final int count) {
        if (count == NO_NUMBER) {
            // nothing to do
        } else {
            String s = String.valueOf(count);
            mEditTextCount.setText(s);
            mEditTextCount.setSelection(mEditTextCount.getText().length());
        }
    }

    @Override
    public String getKey() {
        return mTitle;
    }

    @Override
    public String getValue() {
        return mEditTextCount.getText().toString();
    }

    @Override
    public boolean isValid() {
        return mCount != NO_NUMBER || isOptionnal();
    }

    @Override
    public boolean isEmpty() { return !(mCount != NO_NUMBER); }

    @Override
    public boolean isOptionnal() {
        return mIsOptionnal;
    }

    @Override
    public void isWrong() {
        mTextViewTitle.setText(Html.fromHtml(mTitle + "<bold><font color='" + getResources().getColor(org.argus.sms.app.R.color.error_red) + "'> *</font></bold>"), TextView.BufferType.SPANNABLE);
        onContentChanged();
    }

    @Override
    public void setZero() {
        mCount = 0;
        displayCount(mCount);
    }

    @Override
    public void beforeTextChanged(final CharSequence s, final int start, final int count, final int after) {

    }

    @Override
    public void onTextChanged(final CharSequence s, final int start, final int before, final int count) {
        mTextViewTitle.setText(mTitle);
        onContentChanged();
    }

    @Override
    public void afterTextChanged(final Editable s) {
        String value = s.toString();
        countChanged(value);
    }

    /**
     * Verify if the constraint is valide, alse reverse change
     * @param value the new value
     */
    private void countChanged(String value)  {
        if (mConstraints == null || HelperConstraint.ConstraintsAreRespected(value, mConstraints)) {
            if (TextUtils.isEmpty(value)) {
                mCount = NO_NUMBER;
            } else {
                mCount = Integer.parseInt(value);
            }
            onContentChanged();
        } else {
            if (mCount == NO_NUMBER) {
                mCount = 0;
            }
            // Need remove textChangeListener to avoid infinit loop
            mEditTextCount.removeTextChangedListener(this);
            mEditTextCount.setText(String.valueOf(mCount));
            mEditTextCount.addTextChangedListener(this);

            mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, mContext, getConstraintError(), Toast.LENGTH_LONG, Gravity.CENTER);
        }
    }

    private String getConstraintError() {
        StringBuilder result = new StringBuilder(mContext.getString(R.string.field_constraint_error_prefix));
        result.append(" ");

        for (Pair<HelperConstraint.Constraint, ViewReportNumber> pair : mConstraints) {
            result.append(mContext.getString(HelperConstraint.ConstraintName[pair.first.getValue()]));
            result.append(" ");
            result.append(pair.second.getKey());
            result.append(", ");
        }
        result.delete(result.length() - 2, result.length() - 1);
        return result.toString();
    }

    /**
     * add a constraint
     * @param constraint the constraint type
     * @param vrn the ViewReportNumber linked with this one
     */
    public void addConstraint(HelperConstraint.Constraint constraint, ViewReportNumber vrn) {
        if (mConstraints == null) {
            mConstraints = new ArrayList<>();
            mTextViewTitle.setText(mTitle + " ");
            // setDrawableInTextView(mTextViewTitle, R.drawable.exclamation_blue);
        }

        Pair<HelperConstraint.Constraint, ViewReportNumber> pair = new Pair<HelperConstraint.Constraint, ViewReportNumber>(constraint, vrn);
        if (!mConstraints.contains(pair))
            mConstraints.add(pair);
    }

    /**
     * Function used to add icon for constraint field
     * @param view
     * @param ressource
     */
    /*
    @SuppressLint("NewApi")
    private void setDrawableInTextView(final TextView view, final int ressource) {
        int currentapiVersion = android.os.Build.VERSION.SDK_INT;
        if (currentapiVersion >= android.os.Build.VERSION_CODES.JELLY_BEAN_MR1){
            view.setCompoundDrawablesRelativeWithIntrinsicBounds(0, 0, ressource, 0);
        } else{
            view.setCompoundDrawablesWithIntrinsicBounds(0, 0, ressource, 0);
        }
    }*/
}
