package org.argus.sms.app.model;

import android.test.AndroidTestCase;

import java.util.ArrayList;

import org.argus.sms.app.config.ConfigTestData;
import org.argus.sms.app.parser.Parser;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class SmsTest extends AndroidTestCase {

    private Config mConfig;

    @Override
    protected void setUp() throws Exception {
        super.setUp();
        mConfig= ConfigTestData.getConfig();
    }

    public Sms getRandomSms(int seed){
        Sms sms = new Sms();
        sms.mType = TypeSms.CONFIRM;
        sms.mDiasease = "rougeole";
        sms.mWeek = "12";
        sms.mMonth = "6";
        sms.mYear = "2014";
        sms.mId = seed++;
        sms.mTimestamp = seed+1;
        sms.mStatus = Status.SENT;
        return sms;
    }

    public void testSmsSerialisationWithoutAdditionnalFields(){
        Sms s1 = getRandomSms(12);
        String text =  s1.toSms(TypeSms.WEEKLY,mConfig, true);
        assertEquals("REPORT DISEASE=rougeole , YEAR=2014 , WEEK=12 , ANDROIDID=12",text);
        text =  s1.toSms(TypeSms.MONTHLY,mConfig, true);
        assertEquals("REPORTMONTHY DISEASE=rougeole , YEAR=2014 , MONTH=6 , ANDROIDID=12",text);
    }

    public void testSmsSerialisation(){
        Sms s1 = getRandomSms(12);
        s1.mListData = new ArrayList<Parser.ConfigField>();
        s1.mListData.add(new Parser.ConfigField("cas","4"));
        s1.mListData.add(new Parser.ConfigField("casVaccines","1"));
        String text =  s1.toSms(TypeSms.WEEKLY,mConfig, true);
        assertEquals("REPORT DISEASE=rougeole , YEAR=2014 , WEEK=12 , cas=4 , casVaccines=1 , ANDROIDID=12",text);
        text =  s1.toSms(TypeSms.MONTHLY,mConfig, true);
        assertEquals("REPORTMONTHY DISEASE=rougeole , YEAR=2014 , MONTH=6 , cas=4 , casVaccines=1 , ANDROIDID=12",text);
    }

    public void testSmsFromWeekText(){
        String sms = "REPORT DISEASE=rougeole , YEAR=2014 , WEEK=12 , cas=4 , casVaccines=1";
        Sms s1 = Parser.getSmsFromText(sms,mConfig);
        //The parser think it's a confirm
        s1.mType = TypeSms.WEEKLY;
        assertEquals(TypeSms.WEEKLY,s1.mType);
        assertEquals("rougeole",s1.mDiasease);
        assertEquals("12",s1.mWeek);
        assertEquals(null,s1.mMonth);
        assertEquals("2014",s1.mYear);
        assertEquals(0,s1.mId);
        assertEquals(0,s1.mTimestamp);
        assertEquals(null,s1.mStatus);
        assertEquals(2,s1.mListData.size());
        assertEquals("cas",s1.mListData.get(0).Name);
        assertEquals("4",s1.mListData.get(0).Type);
        assertEquals("casVaccines",s1.mListData.get(1).Name);
        assertEquals("1",s1.mListData.get(1).Type);
    }

    public void testSmsFromMonthText(){
        String sms = "REPORTMONTHY DISEASE=rougeole , YEAR=2014 , WEEK=12 , cas=4 , casVaccines=1";
        Sms s1 = Parser.getSmsFromText(sms,mConfig);
        //The parser think it's a confirm
        s1.mType = TypeSms.MONTHLY;
        assertEquals(TypeSms.MONTHLY,s1.mType);
        assertEquals("rougeole",s1.mDiasease);
        assertEquals("12",s1.mWeek);
        assertEquals(null,s1.mMonth);
        assertEquals("2014",s1.mYear);
        assertEquals(0,s1.mId);
        assertEquals(0,s1.mTimestamp);
        assertEquals(null,s1.mStatus);
        assertEquals(2,s1.mListData.size());
        assertEquals("cas",s1.mListData.get(0).Name);
        assertEquals("4",s1.mListData.get(0).Type);
        assertEquals("casVaccines",s1.mListData.get(1).Name);
        assertEquals("1",s1.mListData.get(1).Type);
    }

    public void testWeekTextToSmSToText() {
        String sms = "REPORT DISEASE=rougeole , YEAR=2014 , WEEK=12 , cas=4 , casVaccines=1 , ANDROIDID=0";
        Sms s1 = Parser.getSmsFromText(sms,mConfig);
        String sms2 = s1.toSms(TypeSms.WEEKLY,mConfig, true);
        assertEquals(sms,sms2);
    }

    public void testMonthlyTextToSmSToText() {
        String sms = "REPORTMONTHY DISEASE=rougeole , YEAR=2014 , MONTH=3 , cas=4 , casVaccines=1 , ANDROIDID=0";
        Sms s1 = Parser.getSmsFromText(sms,mConfig);
        String sms2 = s1.toSms(TypeSms.MONTHLY,mConfig, true);
        assertEquals(sms,sms2);
    }


}
