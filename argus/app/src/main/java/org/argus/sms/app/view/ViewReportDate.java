package org.argus.sms.app.view;

import android.content.Context;
import android.support.v4.app.FragmentManager;
import android.support.v7.widget.CardView;
import android.text.Html;
import android.util.AttributeSet;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.TextView;

import com.roomorama.caldroid.CaldroidFragment;
import com.roomorama.caldroid.CaldroidListener;

import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;

import org.argus.sms.app.R;
import org.argus.sms.app.utils.HelperCalendar;

/**
 * Custom view for report date
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ViewReportDate extends AbstractViewReport implements View.OnClickListener {

    public static final String NO_TEXT = "-";
    private CardView        mCardViewDate;
    private TextView        mTextViewDate;
    private TextView        mTextViewTitle;
    private CardView        mCalendarIC;
    private String          mDate;
    private String          mTitle;
    private FragmentManager mFragmentManager;
    private boolean         mIsOptionnal = false;

    public ViewReportDate(final Context context) {
        super(context);
        init(context, null, null, NO_TEXT);
    }

    public ViewReportDate(final Context context, final AttributeSet attrs) {
        super(context, attrs);
        init(context, null, null, NO_TEXT);
    }

    public ViewReportDate(final Context context, final FragmentManager fragmentManager, final String title, boolean isOpt) {
        super(context);
        init(context, fragmentManager, title, NO_TEXT);
        mIsOptionnal = isOpt;
    }

    public ViewReportDate(final Context context, final FragmentManager fragmentManager, final String title, final String date) {
        super(context);
        init(context, fragmentManager, title, date);
    }

    private void init(final Context context, final FragmentManager fragmentManager, String title, String date) {
        LayoutInflater li = LayoutInflater.from(context);

        li.inflate(R.layout.report_date, this, true);
        mTextViewTitle = (TextView) findViewById(R.id.report_date_TextView_text);
        mCardViewDate = (CardView) findViewById(R.id.report_date_CardView_date);
        mTextViewDate = (TextView) findViewById(R.id.report_date_TextView_date);
        mCalendarIC = (CardView) findViewById(R.id.fragment_alert_CardView_calendar);
        mCardViewDate.setOnClickListener(this);
        mCalendarIC.setOnClickListener(this);
        mTitle = title;
        mTextViewTitle.setText(title);
        mDate = date;
        mFragmentManager = fragmentManager;
        displayCount(date);
        setOnClickListener(this);
    }

    @Override
    public void onClick(final View view) {
        hideKeyboard();
        if (mCardViewDate == view || mCalendarIC == view) {
            onDateClicked();
        }
    }

    /**
     * Handle the date click
     */
    private void onDateClicked() {

        mTextViewTitle.setText(mTitle);
        onContentChanged();

        final CaldroidFragment dialogCaldroidFragment = HelperCalendar.initCalDroid(getContext());
        dialogCaldroidFragment.setMaxDate(Calendar.getInstance().getTime());
        dialogCaldroidFragment.setCaldroidListener(new CaldroidListener() {
            @Override
            public void onSelectDate(Date date, View view) {

                SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy");
                mDate = sdf.format(date);
                displayCount(mDate);
                dialogCaldroidFragment.dismiss();
            }
        });
        dialogCaldroidFragment.show(mFragmentManager, "TAG");
    }

    /**
     * Display the content
     * @param date
     */
    private void displayCount(final String date) {
        mTextViewDate.setText(date);
        onContentChanged();
        mTextViewDate.setError(null);
    }

    @Override
    public String getKey() {
        return mTitle;
    }

    @Override
    public String getValue() {
        return mTextViewDate.getText().toString();
    }

    @Override
    public boolean isValid() {
        return !mDate.equals(NO_TEXT) || isOptionnal();
    }

    @Override
    public boolean isEmpty() {
        return mDate.equals(NO_TEXT);
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
        mDate = NO_TEXT;
        displayCount(mDate);
    }
}
