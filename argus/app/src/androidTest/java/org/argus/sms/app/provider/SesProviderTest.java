package org.argus.sms.app.provider;

import android.database.Cursor;
import android.net.Uri;
import android.test.ProviderTestCase2;
import android.test.mock.MockContentResolver;

import org.argus.sms.app.config.ConfigTestData;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.TypeSms;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class SesProviderTest extends ProviderTestCase2 {

    private MockContentResolver mContentResolver;
    private Config mConfig;

    public SesProviderTest() {
        super(SesProvider.class, SesContract.CONTENT_AUTHORITY);
    }

    @Override
    protected void setUp() throws Exception {
        super.setUp();
        mConfig = ConfigTestData.getConfig();
        mContentResolver = getMockContentResolver();
    }


    public Sms getRandomSms(int seed) {
        Sms sms = new Sms();
        sms.mType = TypeSms.CONFIRM;
        sms.mDiasease = "rougeole";
        sms.mWeek = "rougeole";
        sms.mMonth = "rougeole";
        sms.mYear = "rougeole";
        sms.mId = seed++;
        sms.mTimestamp = seed++;
        sms.mStatus = Status.SENT;
        return sms;
    }


    public void testInsertAndQuery() {

        Sms sms = getRandomSms(12);
        Uri insertedUri = mContentResolver.insert(SesContract.Sms.CONTENT_URI, sms.getContentValues(mConfig));
        assertNotNull(insertedUri);
        Cursor c = mContentResolver.query(insertedUri, null, null, null, null);
        assertNotNull(c);
        assertTrue(c.moveToFirst());
        assertEquals(1, c.getCount());
        Sms fromDatabase = new Sms(mConfig, c);
        assertEquals(sms, fromDatabase);
    }

    public void testInsertTwoAndQueryOne() {

        Sms sms = getRandomSms(12);
        Uri insertedUri = mContentResolver.insert(SesContract.Sms.CONTENT_URI, sms.getContentValues(mConfig));
        sms = getRandomSms(20);
        insertedUri = mContentResolver.insert(SesContract.Sms.CONTENT_URI, sms.getContentValues(mConfig));
        assertNotNull(insertedUri);
        Cursor c = mContentResolver.query(insertedUri, null, null, null, null);
        assertNotNull(c);
        assertTrue(c.moveToFirst());
        assertEquals(1, c.getCount());
        Sms fromDatabase = new Sms(mConfig, c);
        assertEquals(sms, fromDatabase);
    }

    public void testInsertTwoAndQueryAll() {

        Sms sms = getRandomSms(12);
        Uri insertedUri = mContentResolver.insert(SesContract.Sms.CONTENT_URI, sms.getContentValues(mConfig));
        sms = getRandomSms(20);
        insertedUri = mContentResolver.insert(SesContract.Sms.CONTENT_URI, sms.getContentValues(mConfig));
        assertNotNull(insertedUri);
        Cursor c = mContentResolver.query(SesContract.Sms.CONTENT_URI, null, null, null, null);
        assertNotNull(c);
        assertTrue(c.moveToFirst());
        assertEquals(2, c.getCount());
        assertTrue(c.moveToNext());
        Sms fromDatabase = new Sms(mConfig, c);
        assertEquals(sms, fromDatabase);
    }

}
