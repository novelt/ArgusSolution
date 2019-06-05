package org.argus.sms.app.fragment;

import android.content.ContentValues;
import android.database.Cursor;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.List;
import butterknife.ButterKnife;
import butterknife.InjectView;
import org.argus.sms.app.R;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperCalendar;
import org.argus.sms.app.utils.HelperReportSent;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.UpdateAsyncQueryHandler;
import org.argus.sms.app.view.ViewWeekFromTo;

/**
 * Report Weekly Fragment. Specific for a week report
 *
 * This class inherit and overrides methods from {@link AbstractFragmentReport}
 * and from {@link AbstractFragmentSend}
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentReportWeekly extends AbstractFragmentReport implements View.OnClickListener {

    @InjectView(R.id.fragment_report_LinearLayout) protected LinearLayout mLinearLayout;
    @InjectView(R.id.fragment_report_CardView_calendar) protected View mCalendar;
   // @InjectView(R.id.fragment_report_TextView_title) protected TextView mTextViewTitle;
    @InjectView(R.id.fragment_report_Button_send) protected ImageButton mButtonSend;
    @InjectView(R.id.fragment_report_weekly_ViewWeekFromTo) protected ViewWeekFromTo mViewWeekFromTo;
    private int mWeekSelected = -1;
    private int mYearSelected = -1;

    // Added to select previous month as default in all case
    private boolean     mIsFirstDate = true;

    public static FragmentReportWeekly newInstance() {
        FragmentReportWeekly fragment = new FragmentReportWeekly();
        return fragment;
    }

    public FragmentReportWeekly() {
        // Required empty public constructor
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View mainView = inflater.inflate(R.layout.fragment_report_weekly, container, false);
        ButterKnife.inject(this, mainView);
        //mTextViewTitle.setText(Config.getInstance(mContext).getValueForKey(Config.KEYWORD_WEEKLY));
        mButtonSend.setOnClickListener(this);
        mCalendar.setOnClickListener(this);

        Calendar c = HelperCalendar.getCalendarWithCorrectStartWeek(getContext());
        c.add(Calendar.WEEK_OF_YEAR, -1);
        saveAndDisplay(c.getTime());

        return mainView;
    }



    @Override
    protected boolean isDateConfigured() {
        return mWeekSelected!=-1;
    }

    @Override
    protected LinearLayout getItemsContainer() {
        return mLinearLayout;
    }



    @Override
    protected TypeSms getTypeSms() {
        return TypeSms.WEEKLY;
    }

    @Override
    protected int getSendingMessage() {
        return R.string.sending_report_weekly;
    }

    @Override
    protected int getSendSuccessMessage() {
        return R.string.sent_report_weekly;
    }

    @Override
    public void onClick(final View view) {
        if (mCalendar == view) {
            onClickCalendar();
        }else if (mButtonSend == view) {
            onSendClicked();
        }else{
            super.onClick(view);
        }
    }

    @Override
    protected void loadDateFromCursor(final Cursor cursor) {
        if (!mIsFirstDate) {
            int selectedMonth = cursor.getInt(cursor.getColumnIndex(SesContract.Sms.MONTH));
            int selectedWeek = cursor.getInt(cursor.getColumnIndex(SesContract.Sms.WEEK));
            int selectedYear = cursor.getInt(cursor.getColumnIndex(SesContract.Sms.YEAR));
            Calendar c = HelperCalendar.getCalendarWithCorrectStartWeek(mContext);
            c.clear();

            // ANDROID (B2.17) - Particular case : if first days of year attached to last week of previous year
            if (selectedMonth != 0 && selectedMonth < 2 && selectedWeek > 51) {
                selectedYear--;
            }

            if (selectedWeek < 2 && selectedMonth > 11) {
                selectedYear++;
            }

            c.set(Calendar.YEAR, selectedYear);
            c.set(Calendar.WEEK_OF_YEAR, selectedWeek);
            Date d = c.getTime();

            // Used to look for already sent report
            getLoaderManager().destroyLoader(HelperReportSent.REPORT_IS_SENT_LOADER);
            HelperReportSent hrr = new HelperReportSent(mContext, TypeSms.WEEKLY, Integer.toString(selectedWeek), Integer.toString(selectedYear), 0, this);
            getLoaderManager().initLoader(HelperReportSent.REPORT_IS_SENT_LOADER, null, hrr);

            saveAndDisplay(d);
        } else
            mIsFirstDate = false;
    }

    @Override
    protected void configureDraftSmsWithDefaultFields(final Sms sms) {
        Calendar calendar  = HelperCalendar.getCalendarWithCorrectStartWeek(mContext);
        calendar.add(Calendar.WEEK_OF_YEAR,-1);
        sms.mYear = String.valueOf(calendar.get(Calendar.YEAR));
        sms.mWeek = String.valueOf(calendar.get(Calendar.WEEK_OF_YEAR));
    }

    private void onClickCalendar() {
        mListener.onShowCalendar();

        // Used to look for already sent report
        mAlreadySent = false;
    }

    @Override
    public void setCalendarNumber(final Date selectedDate) {
        saveAndDisplay(selectedDate);
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy");
        SimpleDateFormat mdf = new SimpleDateFormat("M");
        int monthNumber = Integer.parseInt(mdf.format(selectedDate));

        ContentValues cv = new ContentValues();
        if (monthNumber < 2 && mWeekSelected > 5) {
            cv.put(SesContract.Sms.YEAR, ((Integer) (Integer.parseInt(sdf.format(selectedDate)) - 1)).toString());
            cv.put(SesContract.Sms.MONTH, "12");
        } else {
            cv.put(SesContract.Sms.YEAR, sdf.format(selectedDate));
            cv.put(SesContract.Sms.MONTH, mdf.format(selectedDate));
        }
        cv.put(SesContract.Sms.WEEK, mWeekSelected);
        new UpdateAsyncQueryHandler(mContext.getContentResolver()).startUpdate(13, null, SesContract.Sms.CONTENT_URI, cv, getSelectionDraft(), getSelectionArgsDraft());
    }

    private void saveAndDisplay(final Date selectedDate) {
        mViewWeekFromTo.displayDate(mContext,selectedDate);
        mWeekSelected = mViewWeekFromTo.getWeekNumber();
        mYearSelected = mViewWeekFromTo.getYear();
    }

    @Override
    protected int getSelectedYear() {
        return mYearSelected;
    }

    @Override
    protected int getSelectedMonth() {
        return 0;
    }

    @Override
    protected int getSelectedWeek() {
        return mWeekSelected;
    }

    @Override
    protected SubTypeSms getSubType() {
        return SubTypeSms.MODEL_WEEKLY;
    }

    /**
     * @param list
     * @return human readable message
     */
    @Override
    public String getConfirmMessage(List<Sms> list) {
        String message = HelperSms.getFormatMessageForActionConfirmation(list);
        return getString(R.string.send_report_weekly,mWeekSelected,mYearSelected,message);
    }


    public void loadSendFromHistory(TypeSms type, String year, String week, String month, long timeStamp) {
        getLoaderManager().destroyLoader(HelperReportSent.REPORT_SEND_LOADER);
        HelperReportSent hrr = new HelperReportSent(mContext, TypeSms.WEEKLY, week, year, timeStamp, this);
        getLoaderManager().initLoader(HelperReportSent.REPORT_SEND_LOADER, null, hrr);
    }
}
