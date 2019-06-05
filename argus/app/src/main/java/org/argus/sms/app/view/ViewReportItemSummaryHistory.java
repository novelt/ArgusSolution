package org.argus.sms.app.view;

import android.content.Context;
import android.text.TextUtils;
import android.util.AttributeSet;
import android.widget.ImageView;
import android.widget.TextView;

import org.argus.sms.app.R;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.SubTypeSms;

/**
 * Curstom view for an history item summary
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ViewReportItemSummaryHistory extends ViewReportItemSummary{

    private ImageView mImageViewStatus;
    private TextView mTextViewError;

    public ViewReportItemSummaryHistory(final Context context) {
        super(context);
    }

    public ViewReportItemSummaryHistory(final Context context, final AttributeSet attrs) {
        super(context, attrs);
    }

    public ViewReportItemSummaryHistory(final Context context, final String title, final String fields, final Status status, final SubTypeSms subType, final String smsConfirm) {
        super(context);
        init(context, title, fields,status,subType,smsConfirm);
    }

    protected void init(final Context context, String title, final String fields, final Status status, final SubTypeSms subType, final String smsConfirm) {
        super.init(context, title, fields, status);
        mImageViewStatus = (ImageView) findViewById(R.id.report_item_summary_ImageView_status);
        setStatusState(status);
        mTextViewError = (TextView) findViewById(R.id.report_item_summary_TextView_errorText);
        if (subType == SubTypeSms.THRESHOLD){
            setTextError(smsConfirm);
        }else{
            setTextError(null);
        }
    }

    @Override
    protected int getLayoutId() {
        return R.layout.report_item_summary_history;
    }

    public void setTextError(String text){
        if (TextUtils.isEmpty(text)){
            mTextViewError.setVisibility(GONE);
        }else{
            mCard.setBackgroundResource(R.drawable.card_round_rect_red);
            mTextViewError.setVisibility(VISIBLE);
            mTextViewError.setText(text);
            mTextViewTitle.setTextColor(getResources().getColor(R.color.oms_white));
            mTextViewFields.setTextColor(getResources().getColor(R.color.oms_white));
        }
    }

    /**
     * Set the drawable for a specific status
     * @param status
     */
    @Override
    public void setStatusState(Status status){
        int ressourceId = 0;
        switch (status){
            case UNKNOWN:
            case DRAFT:
                ressourceId = R.drawable.ic_partial;
                break;
            case ERROR:
            case RECEIVED_BUT_NOT_OK:
                ressourceId = R.drawable.ic_error;
                break;
            case RECEIVED:
                ressourceId = R.drawable.ic_received;
                break;
            case SENT:
                ressourceId = R.drawable.ic_sent;
                break;
        }
        mImageViewStatus.setImageResource(ressourceId);
    }
}
