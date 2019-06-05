package org.argus.sms.app.fragment;

import android.content.ContentValues;
import android.database.Cursor;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;

import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.List;
import butterknife.ButterKnife;
import butterknife.InjectView;
import org.argus.sms.app.R;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperReportSent;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.UpdateAsyncQueryHandler;
import org.argus.sms.app.view.DatePickerFragmentMonthYear;

/**
 * Report Monthly Fragment. Specific for a month report
 * <p/>
 * This class inherit and overrides methods from {@link AbstractFragmentReport}
 * and from {@link AbstractFragmentSend}
 * <p/>
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentReportMonthly extends AbstractFragmentReport {

    @InjectView(R.id.fragment_report_LinearLayout)
    protected LinearLayout mLinearLayout;
    @InjectView(R.id.fragment_report_monthly_TextView_date)
    protected TextView mTextViewDate;
    @InjectView(R.id.fragment_report_CardView_calendar)
    protected View mButtonSelectDate;
   // @InjectView(R.id.fragment_report_TextView_title)
    //protected TextView mTextViewTitle;
    @InjectView(R.id.fragment_report_Button_send)
    protected ImageButton mButtonSend;
    private int mMonthSelected = -1;
    private int mYearSelected = -1;

    // Added to select previous month as default in all case
    private boolean     mIsFirstDate = true;

    public static FragmentReportMonthly newInstance() {
        FragmentReportMonthly fragment = new FragmentReportMonthly();
        return fragment;
    }

    public FragmentReportMonthly() {
        // Required empty public constructor
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        Calendar c = new GregorianCalendar();
        mMonthSelected = c.get(Calendar.MONTH);
        mYearSelected = c.get(Calendar.YEAR);
        if (mMonthSelected == 0) {
            mMonthSelected = 12;
            mYearSelected = mYearSelected - 1;
        }
        View mainView = inflater.inflate(R.layout.fragment_report_monthly, container, false);
        ButterKnife.inject(this, mainView);
        mButtonSelectDate.setOnClickListener(this);
        //mTextViewTitle.setText(Config.getInstance(mContext).getValueForKey(Config.KEYWORD_MONTHLY));
        mButtonSend.setOnClickListener(this);
        return mainView;
    }

    @Override
    public void onClick(final View view) {
        if (mButtonSend == view) {
            onSendClicked();
        } else if (mButtonSelectDate == view) {
            onDateSelected();
        } else {
            super.onClick(view);
        }
    }

    private void onDateSelected() {
        DatePickerFragmentMonthYear dpfmy = DatePickerFragmentMonthYear.newInstance(mMonthSelected-1, mYearSelected);
        dpfmy.setListener(new DatePickerFragmentMonthYear.Listener() {
            @Override
            public void onDateSelected(final int month, final int year) {
                mMonthSelected = month + 1;
                mYearSelected = year;
                displayMonthAndYear(mMonthSelected, mYearSelected);
                updateInDatabase();
            }
        });
        dpfmy.show(getFragmentManager(), "dialogdate");
    }

    @Override
    protected boolean isDateConfigured() {
        if (mMonthSelected == -1) {
            return false;
        }
        return true;
    }

    @Override
    protected LinearLayout getItemsContainer() {
        return mLinearLayout;
    }

    @Override
    public void setCalendarNumber(final Date selectedDate) {
        throw new IllegalStateException("Should not be called");
    }

    @Override
    protected TypeSms getTypeSms() {
        return TypeSms.MONTHLY;
    }

    @Override
    protected int getSendingMessage() {
        return R.string.sending_report_monthly;
    }

    @Override
    protected int getSendSuccessMessage() {
        return R.string.sent_report_monthly;
    }

    @Override
    protected int getSelectedYear() {
        return mYearSelected;
    }

    @Override
    protected int getSelectedMonth() {
        return mMonthSelected;
    }

    @Override
    protected int getSelectedWeek() {
        return 0;
    }

    private void updateInDatabase(){
        ContentValues cv = new ContentValues();
        cv.put(SesContract.Sms.YEAR, mYearSelected);
        cv.put(SesContract.Sms.MONTH, mMonthSelected);
        new UpdateAsyncQueryHandler(mContext.getContentResolver()).startUpdate(13, null, SesContract.Sms.CONTENT_URI, cv, getSelectionDraft(), getSelectionArgsDraft());
    }

    @Override
    protected SubTypeSms getSubType() {
        return SubTypeSms.MODEL_MONTHLY;
    }

    @Override
    protected void loadDateFromCursor(final Cursor cursor) {
        // Not needed cause if no date selected cursor give wrong date... works perfectly without that.
        /*if (!mIsFirstDate) {
            int selectedMonth = cursor.getInt(cursor.getColumnIndex(SesContract.Sms.MONTH));
            mMonthSelected = selectedMonth;
            int selectedYear = cursor.getInt(cursor.getColumnIndex(SesContract.Sms.YEAR));
            mYearSelected = selectedYear;
        }
        else
            mIsFirstDate = false;*/

        getLoaderManager().destroyLoader(HelperReportSent.REPORT_IS_SENT_LOADER);
        HelperReportSent hrr = new HelperReportSent(mContext, TypeSms.MONTHLY, Integer.toString(mMonthSelected), Integer.toString(mYearSelected), 0, this);
        getLoaderManager().initLoader(HelperReportSent.REPORT_IS_SENT_LOADER, null, hrr);

        displayMonthAndYear(mMonthSelected, mYearSelected);
    }

    @Override
    protected void configureDraftSmsWithDefaultFields(final Sms sms) {
        Calendar calendar = Calendar.getInstance();
        calendar.add(Calendar.MONTH,-1);
        sms.mYear = String.valueOf(calendar.get(Calendar.YEAR));
        sms.mMonth = ((Integer)mMonthSelected).toString();
    }

    @Override
    public String getConfirmMessage(List<Sms> list) {
        String message = HelperSms.getFormatMessageForActionConfirmation(list);
        return getString(R.string.send_report_monthly, mMonthSelected, mYearSelected,message);
    }

    private void displayMonthAndYear(int month, int year) {
        mTextViewDate.setText(getString(R.string.month_year, month, year));
    }

    public void loadSendFromHistory(TypeSms type, String year, String week, String month, long timeStamp) {
        getLoaderManager().destroyLoader(HelperReportSent.REPORT_SEND_LOADER);
        HelperReportSent hrr = new HelperReportSent(mContext, TypeSms.MONTHLY, month, year, timeStamp, this);
        getLoaderManager().initLoader(HelperReportSent.REPORT_SEND_LOADER, null, hrr);
    }

}
