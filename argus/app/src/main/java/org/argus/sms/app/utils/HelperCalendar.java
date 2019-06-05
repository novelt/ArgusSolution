package org.argus.sms.app.utils;

import android.content.Context;
import android.os.Bundle;

import com.roomorama.caldroid.CaldroidFragment;

import java.util.Calendar;
import java.util.Date;
import java.util.Locale;

/**
 * Helper for calendar
 * Created by Olivier Goutet.
 * Openium 2014
 */

public class HelperCalendar {

    /**
     * Init the calendar to the correct value
     * @param context application context
     * @return the CaldroidFragment configurated
     */
    public static CaldroidFragment initCalDroid(Context context) {
        Calendar current = getCalendarWithCorrectStartWeek(context);
        final CaldroidFragment dialogCaldroidFragment = CaldroidFragment.newInstance(null, current.get(Calendar.MONTH) + 1, current.get(Calendar.YEAR));
        Bundle b = new Bundle();
        int calendarDayStart = HelperPreference.getConfigWeekStart(context);
        b.putInt(CaldroidFragment.START_DAY_OF_WEEK, calendarDayStart);
        dialogCaldroidFragment.setArguments(b);
        return dialogCaldroidFragment;
    }

    /**
     * Get calendar configurated at the correct week start (from config)
     * @param context application context
     * @return Calendar with the correct startDate
     */
    @SuppressWarnings("ResourceType")
    public static Calendar getCalendarWithCorrectStartWeek(final Context context) {
        int day = HelperPreference.getConfigWeekStart(context);
        Calendar calendar;
        calendar = Calendar.getInstance(Locale.FRENCH);
        // init with the first day of week to have the correct week number
        calendar.setFirstDayOfWeek(day);
        return calendar;
    }

    /****************** This part is used to calculate the week of the selected date ***********************/


    /**
     * Get the week number from a selected date in function of the first day of week mixed with the iso norm.
     * @param ctx the application context
     * @param date the selected date
     * @return the week number
     */
    public static int getWeekFromDate(Context ctx, Date date){
        int day = HelperPreference.getConfigWeekStart(ctx);
        Calendar cal = Calendar.getInstance(Locale.US);
        cal.setTime(date);

        // get the first week day of the selected week
        while (cal.get(Calendar.DAY_OF_WEEK) != day) {
            cal.add(Calendar.DAY_OF_WEEK, -1);
        }
        int firstDayOfSelectedWeek = cal.get(Calendar.DAY_OF_YEAR);

        int yearOfFirstDayOfSelectedWeek = cal.get(Calendar.YEAR);

        cal.add(Calendar.DAY_OF_WEEK, 3);

        int yearOfFourthDayOfSelectedWeek = cal.get(Calendar.YEAR);

        // Get the first 4th day of a week in the year (if first day is monday get the first thursday)
        int fourthDay = ((day + 3) > 7) ? (day + 3) % 8 + 1 : day + 3;
        int firstFirstDayOfWeek = getFirstWeekDayInYear(cal.get(Calendar.YEAR), fourthDay);
        int firstDay = firstFirstDayOfWeek - 3;

        if (yearOfFourthDayOfSelectedWeek != yearOfFirstDayOfSelectedWeek && firstDay < 1)
            return 1;

        return ((firstDayOfSelectedWeek - firstDay) / 7) + 1;
    }

    /**
     * Find the first "day" in the selected year
     * @param year
     * @return
     */
    public static int getFirstWeekDayInYear(int year, int day) {
        Calendar cal = Calendar.getInstance(Locale.US);
        cal.set(Calendar.YEAR, year);
        cal.set(Calendar.MONTH, Calendar.JANUARY);
        cal.set(Calendar.DAY_OF_YEAR, 1);
        while (cal.get(Calendar.DAY_OF_WEEK) != day) {
            cal.add(Calendar.DAY_OF_WEEK, 1);
        }
        return cal.get(Calendar.DAY_OF_MONTH);
    }
}
