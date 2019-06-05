package org.argus.sms.app.helper;

import android.test.AndroidTestCase;

import org.argus.sms.app.utils.HelperCalendar;
import org.argus.sms.app.utils.HelperPreference;

import junit.framework.Assert;

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Calendar;

/**
 * Created by alexandre on 02/02/16.
 */
public class HelperCalendarTest extends AndroidTestCase {

    public void testMonday() {
        DateFormat df = new SimpleDateFormat("MM/dd/yyyy");

        try {
            HelperPreference.saveConfigWeekStart(getContext(), Calendar.MONDAY);
            Assert.assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/04/2016")));
            assertEquals(53, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/01/2016")));
            assertEquals(51, HelperCalendar.getWeekFromDate(getContext(), df.parse("12/16/2015")));
            assertEquals(21, HelperCalendar.getWeekFromDate(getContext(), df.parse("05/22/2015")));
            assertEquals(10, HelperCalendar.getWeekFromDate(getContext(), df.parse("03/10/2016")));
            assertEquals(43, HelperCalendar.getWeekFromDate(getContext(), df.parse("10/30/2016")));
            assertEquals(26, HelperCalendar.getWeekFromDate(getContext(), df.parse("07/01/2016")));
        } catch (Exception e) { e.printStackTrace(); }
    }
    public void testTuesday() {
        DateFormat df = new SimpleDateFormat("MM/dd/yyyy");

        try {
            HelperPreference.saveConfigWeekStart(getContext(), Calendar.TUESDAY);
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/04/2016")));
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/01/2016")));
            assertEquals(51, HelperCalendar.getWeekFromDate(getContext(), df.parse("12/16/2015")));
            assertEquals(21, HelperCalendar.getWeekFromDate(getContext(), df.parse("05/22/2015")));
            assertEquals(11, HelperCalendar.getWeekFromDate(getContext(), df.parse("03/10/2016")));
            assertEquals(44, HelperCalendar.getWeekFromDate(getContext(), df.parse("10/30/2016")));
            assertEquals(27, HelperCalendar.getWeekFromDate(getContext(), df.parse("07/01/2016")));
        } catch (Exception e) { e.printStackTrace(); }
    }

    public void testWednesday() {
        DateFormat df = new SimpleDateFormat("MM/dd/yyyy");

        try {
            HelperPreference.saveConfigWeekStart(getContext(), Calendar.WEDNESDAY);
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/04/2016")));
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/01/2016")));
            assertEquals(51, HelperCalendar.getWeekFromDate(getContext(), df.parse("12/16/2015")));
            assertEquals(21, HelperCalendar.getWeekFromDate(getContext(), df.parse("05/22/2015")));
            assertEquals(11, HelperCalendar.getWeekFromDate(getContext(), df.parse("03/10/2016")));
            assertEquals(44, HelperCalendar.getWeekFromDate(getContext(), df.parse("10/30/2016")));
            assertEquals(27, HelperCalendar.getWeekFromDate(getContext(), df.parse("07/01/2016")));
        } catch (Exception e) { e.printStackTrace(); }
    }

    public void testThursday() {
        DateFormat df = new SimpleDateFormat("MM/dd/yyyy");

        try {
            HelperPreference.saveConfigWeekStart(getContext(), Calendar.THURSDAY);
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/04/2016")));
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/01/2016")));
            assertEquals(21, HelperCalendar.getWeekFromDate(getContext(), df.parse("05/22/2015")));
            assertEquals(50, HelperCalendar.getWeekFromDate(getContext(), df.parse("12/16/2015")));
            assertEquals(11, HelperCalendar.getWeekFromDate(getContext(), df.parse("03/10/2016")));
            assertEquals(27, HelperCalendar.getWeekFromDate(getContext(), df.parse("07/01/2016")));
            assertEquals(44, HelperCalendar.getWeekFromDate(getContext(), df.parse("10/30/2016")));
        } catch (Exception e) { e.printStackTrace(); }
    }

    public void testFriday() {
        DateFormat df = new SimpleDateFormat("MM/dd/yyyy");

        try {
            HelperPreference.saveConfigWeekStart(getContext(), Calendar.FRIDAY);
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/04/2016")));
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/01/2016")));
            assertEquals(21, HelperCalendar.getWeekFromDate(getContext(), df.parse("05/22/2015")));
            assertEquals(50, HelperCalendar.getWeekFromDate(getContext(), df.parse("12/16/2015")));
            assertEquals(10, HelperCalendar.getWeekFromDate(getContext(), df.parse("03/10/2016")));
            assertEquals(27, HelperCalendar.getWeekFromDate(getContext(), df.parse("07/01/2016")));
            assertEquals(44, HelperCalendar.getWeekFromDate(getContext(), df.parse("10/30/2016")));
        } catch (Exception e) { e.printStackTrace(); }
    }

    public void testSaturday() {
        DateFormat df = new SimpleDateFormat("MM/dd/yyyy");

        try {
            HelperPreference.saveConfigWeekStart(getContext(), Calendar.SATURDAY);
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/04/2016")));
            assertEquals(52, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/01/2016")));
            assertEquals(20, HelperCalendar.getWeekFromDate(getContext(), df.parse("05/22/2015")));
            assertEquals(50, HelperCalendar.getWeekFromDate(getContext(), df.parse("12/16/2015")));
            assertEquals(10, HelperCalendar.getWeekFromDate(getContext(), df.parse("03/10/2016")));
            assertEquals(26, HelperCalendar.getWeekFromDate(getContext(), df.parse("07/01/2016")));
            assertEquals(44, HelperCalendar.getWeekFromDate(getContext(), df.parse("10/30/2016")));
        } catch (Exception e) { e.printStackTrace(); }
    }

    public void testSunday() {
        DateFormat df = new SimpleDateFormat("MM/dd/yyyy");

        try {
            HelperPreference.saveConfigWeekStart(getContext(), Calendar.SUNDAY);
            assertEquals(1, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/04/2016")));
            assertEquals(52, HelperCalendar.getWeekFromDate(getContext(), df.parse("01/01/2016")));
            assertEquals(20, HelperCalendar.getWeekFromDate(getContext(), df.parse("05/22/2015")));
            assertEquals(50, HelperCalendar.getWeekFromDate(getContext(), df.parse("12/16/2015")));
            assertEquals(10, HelperCalendar.getWeekFromDate(getContext(), df.parse("03/10/2016")));
            assertEquals(26, HelperCalendar.getWeekFromDate(getContext(), df.parse("07/01/2016")));
            assertEquals(44, HelperCalendar.getWeekFromDate(getContext(), df.parse("10/30/2016")));
        } catch (Exception e) { e.printStackTrace(); }
    }

}
