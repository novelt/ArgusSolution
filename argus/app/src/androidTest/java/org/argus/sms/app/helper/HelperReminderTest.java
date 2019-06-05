package org.argus.sms.app.helper;

import android.content.ContentValues;
import android.test.ProviderTestCase2;
import android.test.mock.MockContentResolver;

import java.util.Calendar;

import org.argus.sms.app.config.ConfigTestData;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.provider.SesProvider;
import org.argus.sms.app.utils.HelperCalendar;
import org.argus.sms.app.utils.HelperReminder;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class HelperReminderTest extends ProviderTestCase2 {

    private MockContentResolver mContentResolver;
    private Config mConfig;
    private Calendar mCalendar;

    public HelperReminderTest() {
        super(SesProvider.class, SesContract.CONTENT_AUTHORITY);
    }

    @Override
    protected void setUp() throws Exception {
        super.setUp();
        mConfig = ConfigTestData.getConfig();
        mContentResolver = getMockContentResolver();
        mCalendar = HelperCalendar.getCalendarWithCorrectStartWeek(getContext());
    }

    public void testNoReminderWeek() {
        ContentValues cv = new ContentValues();
        cv.put(SesContract.Sms.WEEK, mCalendar.get(Calendar.WEEK_OF_YEAR)-1);
        cv.put(SesContract.Sms.YEAR, mCalendar.get(Calendar.YEAR));
        cv.put(SesContract.Sms.STATUS, Status.RECEIVED.toInt());
        assertNotNull(mContentResolver.insert(SesContract.Sms.CONTENT_URI, cv));
        assertTrue(HelperReminder.isReportSentForLastWeek(getContext(),mContentResolver));
    }

    public void testReminderWeek() {
        ContentValues cv = new ContentValues();
        cv.put(SesContract.Sms.WEEK, mCalendar.get(Calendar.WEEK_OF_YEAR)-2);
        cv.put(SesContract.Sms.YEAR, mCalendar.get(Calendar.YEAR));
        cv.put(SesContract.Sms.STATUS, Status.RECEIVED.toInt());
        assertNotNull(mContentResolver.insert(SesContract.Sms.CONTENT_URI, cv));
        assertFalse(HelperReminder.isReportSentForLastWeek(getContext(),mContentResolver));
    }

    public void testNoReminderMonth() {
        ContentValues cv = new ContentValues();
        int month = mCalendar.get(Calendar.MONTH);
        int year = mCalendar.get(Calendar.YEAR);
        cv.put(SesContract.Sms.MONTH, month);
        cv.put(SesContract.Sms.YEAR, year);
        cv.put(SesContract.Sms.STATUS, Status.RECEIVED.toInt());
        assertNotNull(mContentResolver.insert(SesContract.Sms.CONTENT_URI, cv));
        assertTrue(HelperReminder.isReportSentForLastMonth(getContext(),mContentResolver));
    }

    public void testReminderMonth() {
        mCalendar.add(Calendar.WEEK_OF_YEAR,-2);
        int week = mCalendar.get(Calendar.WEEK_OF_YEAR);
        int year = mCalendar.get(Calendar.YEAR);
        ContentValues cv = new ContentValues();
        cv.put(SesContract.Sms.WEEK, week);
        cv.put(SesContract.Sms.YEAR, year);
        cv.put(SesContract.Sms.STATUS, Status.RECEIVED.toInt());
        assertNotNull(mContentResolver.insert(SesContract.Sms.CONTENT_URI, cv));
        assertFalse(HelperReminder.isReportSentForLastMonth(getContext(),mContentResolver));
    }
}
