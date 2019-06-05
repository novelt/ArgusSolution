package org.argus.sms.app.view;

import android.content.Context;
import android.support.v4.app.FragmentManager;
import android.util.AttributeSet;
import android.view.LayoutInflater;
import android.widget.FrameLayout;
import android.widget.TextView;

import java.util.Calendar;
import java.util.Date;

import org.argus.sms.app.R;
import org.argus.sms.app.utils.HelperCalendar;

/**
 * Custom view for week from to
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ViewWeekFromTo extends FrameLayout {

    public static final String NO_TEXT = "-";
    protected TextView mTextViewWeekNumber;
    protected TextView mTextViewYear ;
    protected TextView mTextViewFrom;
    protected TextView mTextViewTo;
    private FragmentManager mFragmentManager;
    private int mWeekSelected;
    private int mYearSelected;

    public ViewWeekFromTo(final Context context) {
        super(context);
        init(context, null);
    }

    public ViewWeekFromTo(final Context context, final AttributeSet attrs) {
        super(context, attrs);
        init(context, null);
    }

    public ViewWeekFromTo(final Context context, final Date initDate) {
        super(context);
        init(context, initDate);
    }


    private void init(final Context context, final Date initDate) {
        LayoutInflater li = LayoutInflater.from(context);
        li.inflate(R.layout.widget_week_from_to, this, true);
        mTextViewWeekNumber = (TextView) findViewById(R.id.widget_week_from_to_TextView_week);
        mTextViewFrom = (TextView) findViewById(R.id.widget_week_from_to_TextView_from);
        mTextViewTo = (TextView) findViewById(R.id.widget_week_from_to_TextView_to);
        mTextViewYear = (TextView) findViewById(R.id.widget_week_from_to_TextView_year);
        displayDate(context,initDate);
    }

    /**
     * Display the week date (from/to) for a specified initDate
     * @param ctx context
     * @param initDate date to init
     */
    public void displayDate(final Context ctx, final Date initDate) {
        if (initDate != null) {
            Calendar c = HelperCalendar.getCalendarWithCorrectStartWeek(ctx);
            c.setTime(initDate);

            int week = HelperCalendar.getWeekFromDate(ctx, initDate);

            mWeekSelected = week;//c.get(Calendar.WEEK_OF_YEAR);
            mYearSelected = c.get(Calendar.YEAR);

            if ((c.get(Calendar.MONTH) +1) < 2 && mWeekSelected > 5) {
                mYearSelected--;
            }

            if (mWeekSelected < 2 && (c.get(Calendar.MONTH) + 1) > 11) {
                mYearSelected++;
            }

            mTextViewWeekNumber.setText(String.valueOf(mWeekSelected));
            mTextViewYear.setText("( "+ mYearSelected +" )");

            c.set(Calendar.HOUR_OF_DAY, 0);
            c.clear(Calendar.MINUTE);
            c.clear(Calendar.SECOND);
            c.clear(Calendar.MILLISECOND);
            c.set(Calendar.DAY_OF_WEEK, c.getFirstDayOfWeek());
            String from = getContext().getString(R.string.format_week_from, c.get(Calendar.DAY_OF_MONTH), c.get(Calendar.MONTH) + 1, c.get(Calendar.YEAR));
            mTextViewFrom.setText(from);
            c.add(Calendar.DAY_OF_WEEK, +6);
            String to = getContext().getString(R.string.format_week_to, c.get(Calendar.DAY_OF_MONTH), c.get(Calendar.MONTH) + 1, c.get(Calendar.YEAR));
            mTextViewTo.setText(to);
        }
    }

    public int getWeekNumber(){
        return mWeekSelected;
    }
    public int getYear(){
        return mYearSelected;
    }
}
