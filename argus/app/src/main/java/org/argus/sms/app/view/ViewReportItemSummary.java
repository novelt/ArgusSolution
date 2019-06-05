package org.argus.sms.app.view;

import android.content.Context;
import android.util.AttributeSet;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;

import org.argus.sms.app.model.Status;

/**
 * Custom view for an item report summary
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public abstract class ViewReportItemSummary extends LinearLayout{

    public static final int NO_NUMBER = Integer.MIN_VALUE;
    protected View mCard;
    protected TextView mTextViewTitle;
    protected TextView mTextViewFields;

    public ViewReportItemSummary(final Context context) {
        super(context);
    }

    public ViewReportItemSummary(final Context context, final AttributeSet attrs) {
        super(context, attrs);
    }

    public ViewReportItemSummary(final Context context, final String title, final String fields, final Status status) {
        super(context);
        init(context, title, fields,status);
    }

    protected void init(final Context context, String title, final String fields, final Status status) {
        LayoutInflater li = LayoutInflater.from(context);
        li.inflate(getLayoutId(), this, true);
        mCard  =  findViewById(org.argus.sms.app.R.id.report_item_summary_card);
        mTextViewTitle = (TextView) findViewById(org.argus.sms.app.R.id.report_item_summary_TextView_title);
        mTextViewFields = (TextView) findViewById(org.argus.sms.app.R.id.report_item_summary_TextView_fields);
        // -------------------------------------------------------------------
        mTextViewTitle.setText(title);
        mTextViewFields.setText(fields);

    }

    protected abstract int getLayoutId();

    public void setText(String text){
        mTextViewTitle.setText(text);
    }

    public void setTextField(String textField){
        mTextViewFields.setText(textField);
    }

    public abstract void setStatusState(Status status);

    public String GetTitle() {
        return mTextViewTitle.getText().toString();
    }
}
