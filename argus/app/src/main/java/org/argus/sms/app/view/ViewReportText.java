package org.argus.sms.app.view;

import android.content.Context;
import android.text.Editable;
import android.text.Html;
import android.text.InputFilter;
import android.text.TextWatcher;
import android.util.AttributeSet;
import android.view.LayoutInflater;
import android.widget.EditText;
import android.widget.TextView;

import org.argus.sms.app.R;
import org.argus.sms.app.utils.SesInputFilter;

/**
 * Custom view to display text
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ViewReportText extends AbstractViewReport implements TextWatcher {

    public static final String  NO_TEXT = "";

    private TextView            mTextViewTitle;
    private EditText            mEditText;
    private String              mTitle;
    private boolean             mIsOptionnal = false;

    public ViewReportText(final Context context) {
        super(context);
        init(context, null, NO_TEXT);
    }

    public ViewReportText(final Context context, final AttributeSet attrs) {
        super(context, attrs);
        init(context, null, NO_TEXT);
    }

    public ViewReportText(final Context context, final String title, boolean isOpt) {
        super(context);
        init(context, title, NO_TEXT);
        mIsOptionnal = isOpt;
    }

    public ViewReportText(final Context context, final String title, final String initText) {
        super(context);
        init(context, title, initText);
    }

    private void init(final Context context, String title, String initText) {
        LayoutInflater li = LayoutInflater.from(context);
        li.inflate(R.layout.report_text, this, true);
        mTextViewTitle = (TextView) findViewById(R.id.report_text_TextView_title);
        mEditText = (EditText) findViewById(R.id.report_text_EditText);
        mEditText.addTextChangedListener(this);
        mEditText.setFilters(new InputFilter[] {new SesInputFilter()});
        mTitle = title;
        displayText(mTitle);

    }

    /**
     * Display the text on the view
     * @param text text to display
     */
    private void displayText(final String text) {
        mTextViewTitle.setText(text);
        onContentChanged();
    }

    @Override
    public String getKey() {
        return mTitle;
    }

    @Override
    public String getValue() {
        return mEditText.getText().toString();
    }

    @Override
    public boolean isValid() {
        return mEditText.length() != 0 || isOptionnal();
    }

    @Override
    public boolean isEmpty() {
        return !(mEditText.length() != 0);
    }

    @Override
    public boolean isOptionnal() {
        return mIsOptionnal;
    }

    @Override
    public void isWrong() {
        mTextViewTitle.setText(Html.fromHtml(mTitle + "<bold><font color='" + getResources().getColor(R.color.error_red) + "'> *</font></bold>"), TextView.BufferType.SPANNABLE);
        onContentChanged();
    }

    @Override
    public void setZero() {
        displayText("");
    }

    @Override
    public void beforeTextChanged(final CharSequence charSequence, final int i, final int i2, final int i3) {

    }

    @Override
    public void onTextChanged(final CharSequence charSequence, final int i, final int i2, final int i3) {
        mTextViewTitle.setText(mTitle);
        onContentChanged();
    }

    @Override
    public void afterTextChanged(final Editable editable) {
        onContentChanged();
    }
}
