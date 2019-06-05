package org.argus.sms.app.view;

import android.content.Context;
import android.util.AttributeSet;
import android.widget.TextView;

import org.argus.sms.app.R;
import org.argus.sms.app.model.Status;

/**
 * Custom view for a report item summary
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ViewReportItemSummaryReport extends ViewReportItemSummary{

    private TextView mTextViewStatus;

    public ViewReportItemSummaryReport(final Context context) {
        super(context);
    }

    public ViewReportItemSummaryReport(final Context context, final AttributeSet attrs) {
        super(context, attrs);
    }

    public ViewReportItemSummaryReport(final Context context, final String title, final String fields, final Status status) {
        super(context);
        init(context, title, fields,status);
    }

    @Override
    protected void init(final Context context, String title, final String fields, final Status status) {
        super.init(context,title,fields,status);
        mTextViewStatus = (TextView) findViewById(R.id.report_item_summary_TextView_status);
        setStatusState(status);
    }

    @Override
    protected int getLayoutId() {
        return R.layout.report_item_summary_report;
    }

    /**
     * Set the status text for a status
     * @param status status to set
     */
    @Override
    public void setStatusState(Status status){
        String text = "?";
        switch (status){
            case UNKNOWN:
            case DRAFT:
                text ="?";
                break;
            case SENT:
                text="OK";
                break;
        }
        mTextViewStatus.setText(text);
    }
}
