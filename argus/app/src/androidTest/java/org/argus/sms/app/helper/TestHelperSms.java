package org.argus.sms.app.helper;

import android.test.AndroidTestCase;

import org.argus.sms.app.config.ConfigTestData;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.parser.TestParseType;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.HelperPreference;

import junit.framework.Assert;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class TestHelperSms extends AndroidTestCase {


    public void testDetectid() {
        String sms1 = "coucou coucou " + Config.KEYWORD_ID + "=12 coudjcksdv vockvdf,vbffdv dfk ";
        String sms2 = Config.KEYWORD_ID + "=12";
        String sms3 = Config.KEYWORD_ID + "12";
        String sms4 = "12";
        String sms5 = "";
        String sms6 = null;

        Assert.assertEquals("12", HelperSms.getIdInString(sms1));
        assertEquals("12", HelperSms.getIdInString(sms2));
        assertNull(HelperSms.getIdInString(sms3));
        assertNull(HelperSms.getIdInString(sms4));
        assertNull(HelperSms.getIdInString(sms5));
        assertNull(HelperSms.getIdInString(sms6));
    }

    public void testHelperSms() {
        String statusList = "1,1,1";
        Assert.assertEquals(Status.SENT, HelperSms.getStatusFromStatusList(statusList));
        statusList = "1,3,1,1";
        assertEquals(Status.PARTIAL, HelperSms.getStatusFromStatusList(statusList));
        statusList = "1,2,1,1";
        assertEquals(Status.ERROR, HelperSms.getStatusFromStatusList(statusList));
        statusList = "1,2,3,1";
        assertEquals(Status.ERROR, HelperSms.getStatusFromStatusList(statusList));

        statusList = "1,3,4,1";
        assertEquals(Status.PARTIAL, HelperSms.getStatusFromStatusList(statusList));

        statusList = "";
        assertEquals(Status.UNKNOWN, HelperSms.getStatusFromStatusList(statusList));
        statusList = null;
        assertEquals(Status.UNKNOWN, HelperSms.getStatusFromStatusList(statusList));
    }

    public void testRemoveAndroidid() {
        String input = "ANDROIDID=12 TEMPLATE-ALERT: ALERT EVENEMENT=String , DATE=String , LIEU=String , CAS=Integer , HOSPITALISES=Integer , DECES=Integer";
        String result = HelperSms.removeAndroidIdFromString(input);
        assertFalse(result.contains(Config.KEYWORD_ID));
        assertEquals("TEMPLATE-ALERT: ALERT EVENEMENT=String , DATE=String , LIEU=String , CAS=Integer , HOSPITALISES=Integer , DECES=Integer", result);

        input = "toto,titi tata";
        result = HelperSms.removeAndroidIdFromString(input);
        assertEquals(input,result);

        input = "toto,ANDROIDID=13titi tata";
        result = HelperSms.removeAndroidIdFromString(input);
        assertEquals("toto,titi tata",result);
    }

    public void testRemoveAndroididAtTheEnd() {

        String input = "toto,titi tata , ANDROIDID=144";
        String result = HelperSms.removeAndroidIdFromString(input);
        assertEquals("toto,titi tata",result);

        input = "toto,titi tata, ANDROIDID=144";
        result = HelperSms.removeAndroidIdFromString(input);
        assertEquals("toto,titi tata",result);

        input = "toto,titi tata,ANDROIDID=144";
        result = HelperSms.removeAndroidIdFromString(input);
        assertEquals("toto,titi tata",result);
    }

    public void testRemoveFirstWord(){
        assertEquals("coucou titi",HelperSms.removeFirstWordInString("test coucou titi"));
        assertEquals("testtiti",HelperSms.removeFirstWordInString("testtiti"));
        assertEquals("",HelperSms.removeFirstWordInString(""));
        assertEquals(null,HelperSms.removeFirstWordInString(null));
    }

    public void testRemoveFirstKeyWord(){
        assertEquals("test coucou titi",HelperSms.removeFirstKeywordInString("toto: test coucou titi"));
        assertEquals("test coucou titi",HelperSms.removeFirstKeywordInString("toto-titi: test coucou titi"));
        assertEquals("test coucou titi",HelperSms.removeFirstKeywordInString("toto@titi: test coucou titi"));
        assertEquals("toto:test coucou titi",HelperSms.removeFirstKeywordInString("toto:test coucou titi"));
        assertEquals("test coucou titi",HelperSms.removeFirstKeywordInString("test coucou titi"));
        assertEquals("",HelperSms.removeFirstKeywordInString(""));
        assertEquals(null,HelperSms.removeFirstKeywordInString(null));
    }

    public void testGetPhoneNumberForKey(){
        String sms = "Server=+33674525427";
        assertEquals("+33674525427",HelperSms.getPhoneNumberForKey(sms,"Server"));
        sms = "CONF: NbMsg=14, NbCharMax=150, Server=+33674525427, WeekStart=1, D4ConfAlert=30, D4ConfW=120, D4ConfM=120";
        assertEquals("+33674525427",HelperSms.getPhoneNumberForKey(sms,"Server"));
    }

    public void testParseMessages(){
        assertNull(HelperPreference.getAlertMessage(getContext()));
        HelperSms.parseSmsIfConfig(getContext(), TestParseType.SMSCONFIG11);
        assertEquals("The alert confirmation wasn't received, please contact our manager",HelperPreference.getAlertMessage(getContext()));

        assertNull(HelperPreference.getWeekMessage(getContext()));
        HelperSms.parseSmsIfConfig(getContext(), TestParseType.SMSCONFIG12);
        assertEquals("The week report confirmation wasn't received, please contact our manager",HelperPreference.getWeekMessage(getContext()));

        assertNull(HelperPreference.getMonthMessage(getContext()));
        HelperSms.parseSmsIfConfig(getContext(), TestParseType.SMSCONFIG13);
        assertEquals("The month report confirmation wasn't received, please contact our manager",HelperPreference.getMonthMessage(getContext()));
    }

    public void testShortenSmsConfirmMessage(){
        Config config = ConfigTestData.getConfig();
        String originalMessage ="Send weekly report from week 23? Content: REPORT DISEASE=DIPTHERIA, YEAR=2015 , WEEK=23 , VACCINATEDCASES=0 , NONVACCINATEDCASES=0 / REPORT DISEASE=HEPATITIS, YEAR=2015, WEEK=23, CASES=0 / REPORT DISEASE=LEPTOSPIROSIS, YEAR=2015, WEEK=23, CASES=0 / REPORT DISEASE=MENINGITIS, YEAR=2015, WEEK=23, CASES=0 / REPORT DISEASE=TIAC, YEAR=2015, WEEK=23, EPISODES2=0 / REPORT DISEASE=TYPHOID, YEAR=2015, WEEK=23, CASES=0, DEATH=0 / REPORT DISEASE=WHOOPINGCOUGH, YEAR=2015, WEEK=23, VACCINATEDCASES=0 , NONVACCINATEDCASES=0";
        String result = HelperSms.shortenSmsConfirmMessage(config,originalMessage);
        String expected ="Send weekly report from week 23? Content: DISEASE=DIPTHERIA, VACCINATEDCASES=0 , NONVACCINATEDCASES=0 / DISEASE=HEPATITIS, CASES=0 / DISEASE=LEPTOSPIROSIS, CASES=0 / DISEASE=MENINGITIS, CASES=0 / DISEASE=TIAC, EPISODES2=0 / DISEASE=TYPHOID, CASES=0, DEATH=0 / DISEASE=WHOOPINGCOUGH, VACCINATEDCASES=0 , NONVACCINATEDCASES=0";
        assertEquals(expected, result);


    }
}
