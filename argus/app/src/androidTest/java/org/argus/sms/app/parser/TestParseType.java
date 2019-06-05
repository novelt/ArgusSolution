package org.argus.sms.app.parser;

import android.test.AndroidTestCase;

import java.util.List;
import java.util.Set;

import org.argus.sms.app.ConfigApp;
import org.argus.sms.app.config.ConfigTestData;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.utils.HelperSms;

import junit.framework.Assert;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class TestParseType extends AndroidTestCase {

    public final static String SMS_INVALIDE1 = "test";
    public final static String SMS_INVALIDE2 = "toto";
    public final static String SMS_INVALIDE3 = "";
    public final static String SMS_INVALIDE4 = null;
    public final static String SMS1 = "REPORT DISEASE=rougeole , YEAR=2014 , WEEK=25 , casvaccines=10 , casnonvaccines=2";
    public final static String SMS2 = "REPORTMONTHY DISEASE=PFA , YEAR=2014 , MONTH=01 , cas=10";
    public final static String SMS3 = "ALERT test1=rougeole";
    public final static String SMS4 = "Envoyez votre rapport";
    public final static String SMS5 = "S-001: Erreur";
    public final static String SMS6 = "S-101: Erreur";
    public final static String SMS7 = "J-001: Erreur";
    public final static String SMS8 = "J-101: Erreur";
    public final static String SMS9 = "W-001: Erreur";
    public final static String SMS10 = "W-101: Erreur";
    public final static String SMS11 = "TEMPLATE-WEEKLY: REPORT DISEASE=rougeole , YEAR=[year] , WEEK=[week] , casvaccines=[number] , casnonvaccines=[number]";
    public final static String SMS12 = "TEMPLATE-MONTHLY: REPORTMONTHY DISEASE=rougeole , YEAR=[year] , MONTH=[month] , casvaccines=[number] , casnonvaccines=[number]";
    public final static String SMS13 = "TEMPLATE-ALERT: ALERT evenement=String , cas=Integer";
    // ------------------------------------------------------------------------
    public final static String SMSCONFIG1 = "TEMPLATE-ALERT: ALERT EVENEMENT=String , DATE=String , LIEU=String , CAS=Integer , HOSPITALISES=Integer , DECES=Integer";
    public final static String SMSCONFIG1_ALT = "ANDROIDID=12 TEMPLATE-ALERT: ALERT EVENEMENT=String , DATE=String , LIEU=String , CAS=Integer , HOSPITALISES=Integer , DECES=Integer";
    public final static String SMSCONFIG2 = "TEMPLATE-WEEKLY: REPORT DISEASE=COQUELUCHE , YEAR=Integer , WEEK=Integer , CASVACCINES=Integer , CASNONVACCINES=Integer";
    public final static String SMSCONFIG2_ALT = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=COQUELUCHE , YEAR=Integer , WEEK=Integer , CASVACCINES=Integer , CASNONVACCINES=Integer";
    public final static String SMSCONFIG3 = "TEMPLATE-WEEKLY: REPORT DISEASE=DIPHTERIE , YEAR=Integer , WEEK=Integer , CASVACCINES=Integer , CASNONVACCINES=Integer";
    public final static String SMSCONFIG4 = "TEMPLATE-WEEKLY: REPORT DISEASE=HEPATITE , YEAR=Integer , WEEK=Integer , CAS=Integer";
    public final static String SMSCONFIG5 = "TEMPLATE-WEEKLY: REPORT DISEASE=LEPTOSPIROSE , YEAR=Integer , WEEK=Integer , CAS=Integer";
    public final static String SMSCONFIG6 = "TEMPLATE-WEEKLY: REPORT DISEASE=MENINGITE , YEAR=Integer , WEEK=Integer , CAS=Integer";
    public final static String SMSCONFIG7 = "TEMPLATE-WEEKLY: REPORT DISEASE=ROUGEOLE , YEAR=Integer , WEEK=Integer , CASVACCINES=Integer , CASNONVACCINES=Integer";
//    public final static String SMSCONFIG8 = "TEMPLATE-WEEKLY-TEST: REPORT DISEASE=TEST , YEAR=Integer , WEEK=Integer , CAS=Integer";
    public final static String SMSCONFIG9 = "TEMPLATE-WEEKLY: REPORT DISEASE=TIAC , YEAR=Integer , WEEK=Integer , EPISODES=Integer , CAS=Integer";
    public final static String SMSCONFIG10 = "TEMPLATE-WEEKLY: REPORT DISEASE=TYPHOIDE , YEAR=Integer , WEEK=Integer , CAS=Integer";
    public final static String SMSCONFIG10_ALT = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=TYPHOIDE , YEAR=Integer , WEEK=Integer , CAS=Integer";
    public final static String SMSCONFIG11 = "CONF: M4ConfAlert=The alert confirmation wasn't received, please contact our manager";
    public final static String SMSCONFIG12 = "CONF: M4ConfW=The week report confirmation wasn't received, please contact our manager";
    public final static String SMSCONFIG13 = "CONF: M4ConfM=The month report confirmation wasn't received, please contact our manager";
    public final static String SMSCONFIG14 = "CONF: NbMsg=14, NbCharMax=150, Server=+33674525427, WeekStart=1, D4ConfAlert=30, D4ConfW=120, D4ConfM=120";
    public final static String SMSCONFIRM1 = "ANDROIDID=270 ANDROID_OK R06: Tout est ok";
    public final static String SMSCONFIRM2 = "ANDROIDID=270 ANDROID_OK R04: Alerte recue, mais non transmisse, veuillez contacter la personne par téléphone.";
    public final static String SMSCONFIRM3 = "ANDROIDID=589 ANDROID_THRESHOLD R01: Le seuil de 1 pour la maladie meningite a été atteint (3) à partir du 24/11/2014";
    public final static String SMSCONFIRM4 = "ANDROIDID=270 ANDROID_OK R05: Alerte ok";

    private Config mConfig;

    @Override
    protected void setUp() throws Exception {
        super.setUp();
        mConfig = ConfigTestData.getConfig();
    }

    public void testType(){
        Assert.assertEquals(TypeSms.CONFIRM, Parser.getTypeFromText(SMS1, mConfig));
        Assert.assertEquals(TypeSms.CONFIRM, Parser.getTypeFromText(SMS2, mConfig));
        Assert.assertEquals(TypeSms.ALERT, Parser.getTypeFromText(SMS3, mConfig));
        Assert.assertEquals(TypeSms.OTHER, Parser.getTypeFromText(SMS4, mConfig));
        Assert.assertEquals(TypeSms.ERROR, Parser.getTypeFromText(SMS5, mConfig));
        Assert.assertEquals(TypeSms.ERROR, Parser.getTypeFromText(SMS6, mConfig));
        Assert.assertEquals(TypeSms.ERROR, Parser.getTypeFromText(SMS7, mConfig));
        Assert.assertEquals(TypeSms.ERROR, Parser.getTypeFromText(SMS8, mConfig));
        Assert.assertEquals(TypeSms.ERROR, Parser.getTypeFromText(SMS9, mConfig));
        Assert.assertEquals(TypeSms.ERROR, Parser.getTypeFromText(SMS10, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMS11, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMS12, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMS13, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG1, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG1_ALT, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG2, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG2_ALT, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG3, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG4, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG5, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG6, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG7, mConfig));
//        assertEquals(TypeSms.MODEL,Parser.getTypeFromText(SMSCONFIG8,mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG9, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG10, mConfig));
        Assert.assertEquals(TypeSms.MODEL, Parser.getTypeFromText(SMSCONFIG10_ALT, mConfig));
        Assert.assertEquals(TypeSms.CONFIG, Parser.getTypeFromText(SMSCONFIG11, mConfig));
        Assert.assertEquals(TypeSms.CONFIG, Parser.getTypeFromText(SMSCONFIG12, mConfig));
        Assert.assertEquals(TypeSms.CONFIG, Parser.getTypeFromText(SMSCONFIG13, mConfig));
        Assert.assertEquals(TypeSms.CONFIG, Parser.getTypeFromText(SMSCONFIG14, mConfig));
        Assert.assertEquals(TypeSms.CONFIRM, Parser.getTypeFromText(SMSCONFIRM1, mConfig));
        Assert.assertEquals(TypeSms.ERROR, Parser.getTypeFromText(SMSCONFIRM2, mConfig));
        Assert.assertEquals(TypeSms.THRESHOLD, Parser.getTypeFromText(SMSCONFIRM3, mConfig));
        Assert.assertEquals(TypeSms.CONFIRM, Parser.getTypeFromText(SMSCONFIRM4, mConfig));
    }

    public void testTypeError(){
        Assert.assertEquals(TypeSms.OTHER, Parser.getTypeFromText(SMS_INVALIDE1, mConfig));
        Assert.assertEquals(TypeSms.OTHER, Parser.getTypeFromText(SMS_INVALIDE2, mConfig));
        assertNull(Parser.getTypeFromText(SMS_INVALIDE3,mConfig));
        assertNull(Parser.getTypeFromText(SMS_INVALIDE4,mConfig));
    }

    public void testParseError(){
        Sms sms = Parser.getSmsFromText(SMS_INVALIDE1,mConfig);
        assertNotNull(sms);
        assertEquals(8,sms.getContentValues(mConfig).size());
        sms = Parser.getSmsFromText(SMS_INVALIDE2,mConfig);
        assertNotNull(sms);
        assertEquals(8,sms.getContentValues(mConfig).size());
        assertNull(Parser.getSmsFromText(SMS_INVALIDE3, mConfig));
        assertNull(Parser.getSmsFromText(SMS_INVALIDE4, mConfig));
    }

    public void testDeseaseInvalidExtract(){
        assertNull(Parser.getFieldFromSms(Config.KEYWORD_DISEASE, null, mConfig));
        assertNull(Parser.getFieldFromSms(Config.KEYWORD_DISEASE,"",mConfig));
        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS1, null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }
        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_DISEASE, null, null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }

        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_DISEASE, "", null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }
    }

    public void testDeseaseExtract(){
        assertEquals("rougeole",Parser.getFieldFromSms(Config.KEYWORD_DISEASE,SMS1,mConfig));
        assertEquals("PFA",Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS2, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_DISEASE,SMS3,mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS4, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS5, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS6, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS7, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS8, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS9, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS10, mConfig));
        assertEquals("rougeole",Parser.getFieldFromSms(Config.KEYWORD_DISEASE, SMS11, mConfig));
        assertEquals("rougeole",Parser.getFieldFromSms(Config.KEYWORD_DISEASE,SMS12,mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_DISEASE,SMS13,mConfig));
    }

    public void testYearInvalidExtract(){
        assertNull(Parser.getFieldFromSms(Config.KEYWORD_YEAR, null, mConfig));
        assertNull(Parser.getFieldFromSms(Config.KEYWORD_YEAR,"",mConfig));
        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS1, null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }
        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_YEAR, null, null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }

        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_YEAR, "", null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }
    }

    public void testYearExtract(){
        assertEquals("2014",Parser.getFieldFromSms(Config.KEYWORD_YEAR,SMS1,mConfig));
        assertEquals("2014",Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS2, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_YEAR,SMS3,mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS4, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS5, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS6, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS7, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS8, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS9, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS10, mConfig));
        assertEquals("[year]",Parser.getFieldFromSms(Config.KEYWORD_YEAR, SMS11, mConfig));
        assertEquals("[year]",Parser.getFieldFromSms(Config.KEYWORD_YEAR,SMS12,mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_YEAR,SMS13,mConfig));
    }

    public void testWeekInvalidExtract(){
        assertNull(Parser.getFieldFromSms(Config.KEYWORD_WEEK, null, mConfig));
        assertNull(Parser.getFieldFromSms(Config.KEYWORD_WEEK,"",mConfig));
        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS1, null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }
        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_WEEK, null, null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }

        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_WEEK, "", null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }
    }

    public void testWeekExtract(){
        assertEquals("25",Parser.getFieldFromSms(Config.KEYWORD_WEEK,SMS1,mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS2, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK,SMS3,mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS4, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS5, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS6, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS7, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS8, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS9, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS10, mConfig));
        assertEquals("[week]",Parser.getFieldFromSms(Config.KEYWORD_WEEK, SMS11, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK,SMS12,mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_WEEK,SMS13,mConfig));
    }

    public void testMonthInvalidExtract(){
        assertNull(Parser.getFieldFromSms(Config.KEYWORD_MONTH, null, mConfig));
        assertNull(Parser.getFieldFromSms(Config.KEYWORD_MONTH,"",mConfig));
        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS1, null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }
        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_MONTH, null, null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }

        try {
            assertNull(Parser.getFieldFromSms(Config.KEYWORD_MONTH, "", null));
            assertTrue("No exception raized",false);
        }catch(IllegalArgumentException e){

        }
    }

    public void testMonthExtract(){
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH,SMS1,mConfig));
        assertEquals("01",Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS2, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH,SMS3,mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS4, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS5, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS6, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS7, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS8, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS9, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS10, mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH, SMS11, mConfig));
        assertEquals("[month]",Parser.getFieldFromSms(Config.KEYWORD_MONTH,SMS12,mConfig));
        assertEquals(null,Parser.getFieldFromSms(Config.KEYWORD_MONTH,SMS13,mConfig));
    }

    public void testOtherDataExtractSize(){
        assertEquals(2,Parser.getOtherFieldsFromSms(SMS1,mConfig).size());
        assertEquals(1,Parser.getOtherFieldsFromSms(SMS2,mConfig).size());
        assertEquals(1,Parser.getOtherFieldsFromSms(SMS3,mConfig).size());
        assertEquals(0,Parser.getOtherFieldsFromSms(SMS4,mConfig).size());
        assertEquals(0,Parser.getOtherFieldsFromSms(SMS5,mConfig).size());
        assertEquals(0,Parser.getOtherFieldsFromSms(SMS6,mConfig).size());
        assertEquals(0,Parser.getOtherFieldsFromSms(SMS7,mConfig).size());
        assertEquals(0,Parser.getOtherFieldsFromSms(SMS8,mConfig).size());
        assertEquals(0,Parser.getOtherFieldsFromSms(SMS9,mConfig).size());
        assertEquals(0,Parser.getOtherFieldsFromSms(SMS10,mConfig).size());
        assertEquals(2,Parser.getOtherFieldsFromSms(SMS11,mConfig).size());
        assertEquals(2,Parser.getOtherFieldsFromSms(SMS12,mConfig).size());
        assertEquals(2,Parser.getOtherFieldsFromSms(SMS13,mConfig).size());

    }

    public void testOtherDataExtractLength(){
        String sms = "ALERT test1=rougeole avec plusieurs champs textuels , test2=autre chose , test3=toto";
        List<Parser.ConfigField> list = Parser.getOtherFieldsFromSms(sms, mConfig);
        assertEquals(3,list.size());
        assertEquals("test1",list.get(0).Name);
        assertEquals("rougeole avec plusieurs champs textuels",list.get(0).Type);
        assertEquals("test2",list.get(1).Name);
        assertEquals("autre chose",list.get(1).Type);
        assertEquals("test3",list.get(2).Name);
        assertEquals("toto",list.get(2).Type);
    }

    public void testOtherDataExtractData() {
        List<Parser.ConfigField> list = Parser.getOtherFieldsFromSms(SMS1, mConfig);
        assertEquals("casvaccines",list.get(0).Name);
        assertEquals("10",list.get(0).Type);
        assertEquals("casnonvaccines",list.get(1).Name);
        assertEquals("2",list.get(1).Type);
        list = Parser.getOtherFieldsFromSms(SMS2, mConfig);
        assertEquals("cas",list.get(0).Name);
        assertEquals("10",list.get(0).Type);
        list = Parser.getOtherFieldsFromSms(SMS3, mConfig);
        assertEquals("test1",list.get(0).Name);
        assertEquals("rougeole",list.get(0).Type);
        list = Parser.getOtherFieldsFromSms(SMS11, mConfig);
        assertEquals("casvaccines",list.get(0).Name);
        assertEquals("[number]",list.get(0).Type);
        assertEquals("casnonvaccines",list.get(1).Name);
        assertEquals("[number]",list.get(1).Type);
        list = Parser.getOtherFieldsFromSms(SMS12, mConfig);
        assertEquals("casvaccines",list.get(0).Name);
        assertEquals("[number]",list.get(0).Type);
        assertEquals("casnonvaccines",list.get(1).Name);
        assertEquals("[number]",list.get(1).Type);
        list = Parser.getOtherFieldsFromSms(SMS13, mConfig);
        assertEquals("evenement",list.get(0).Name);
        assertEquals("String",list.get(0).Type);
        assertEquals("cas",list.get(1).Name);
        assertEquals("Integer",list.get(1).Type);
    }

    public void testIsConfig(){
        assertFalse(Parser.isConfig(SMS_INVALIDE1));
        assertFalse(Parser.isConfig(SMS_INVALIDE2));
        assertFalse(Parser.isConfig(SMS_INVALIDE3));
        assertFalse(Parser.isConfig(SMS_INVALIDE4));
        assertFalse(Parser.isConfig(SMS1));
        assertFalse(Parser.isConfig(SMS2));
        assertFalse(Parser.isConfig(SMS3));
        assertFalse(Parser.isConfig(SMS4));
        assertFalse(Parser.isConfig(SMS5));
        assertFalse(Parser.isConfig(SMS6));
        assertFalse(Parser.isConfig(SMS7));
        assertFalse(Parser.isConfig(SMS8));
        assertFalse(Parser.isConfig(SMS9));
        assertFalse(Parser.isConfig(SMS10));
        assertTrue(Parser.isConfig(SMS11));
        assertTrue(Parser.isConfig(SMS12));
        assertTrue(Parser.isConfig(SMS13));
        assertTrue(Parser.isConfig(SMSCONFIG1));
        assertTrue(Parser.isConfig(SMSCONFIG1_ALT));
        assertTrue(Parser.isConfig(SMSCONFIG2));
        assertTrue(Parser.isConfig(SMSCONFIG2_ALT));
        assertTrue(Parser.isConfig(SMSCONFIG3));
        assertTrue(Parser.isConfig(SMSCONFIG4));
        assertTrue(Parser.isConfig(SMSCONFIG5));
        assertTrue(Parser.isConfig(SMSCONFIG6));
        assertTrue(Parser.isConfig(SMSCONFIG7));
        assertTrue(Parser.isConfig(SMSCONFIG9));
        assertTrue(Parser.isConfig(SMSCONFIG10));
        assertTrue(Parser.isConfig(SMSCONFIG10_ALT));
        assertTrue(Parser.isConfig(SMSCONFIG11));
        assertTrue(Parser.isConfig(SMSCONFIG12));
        assertTrue(Parser.isConfig(SMSCONFIG13));
        assertTrue(Parser.isConfig(SMSCONFIG14));
    }

    public void testParsePhoneNumbers(){
        String sms = "Server=+33674525427" ;
        Set<String> servers = Parser.parsePhoneNumbers(sms);
        assertEquals(1, servers.size());
        assertTrue(servers.contains("+33674525427"));

         sms = "Server=+33674525427/+123456789" ;
        servers = Parser.parsePhoneNumbers(sms);
        assertEquals(2,servers.size());
        assertTrue(servers.contains("+33674525427"));
        assertTrue(servers.contains("+123456789"));

        sms = "CONF: NbMsg=14, NbCharMax=150, Server=+33674525427, WeekStart=1, D4ConfAlert=30, D4ConfW=120, D4ConfM=120";
        servers = Parser.parsePhoneNumbers(sms);
        assertEquals(1,servers.size());
        assertTrue(servers.contains("+33674525427"));

        sms = "CONF: NbMsg=14, NbCharMax=150, Server=+33674525427/+123456789, WeekStart=1, D4ConfAlert=30, D4ConfW=120, D4ConfM=120" ;
        servers = Parser.parsePhoneNumbers(sms);
        assertEquals(2,servers.size());
        assertTrue(servers.contains("+33674525427"));
        assertTrue(servers.contains("+123456789"));
    }

    public void testParseThreshold(){
        assertFalse(HelperSms.isMessageThreshold(SMSCONFIRM2));
        assertTrue(HelperSms.isMessageThreshold(SMSCONFIRM3));
        String sms = HelperSms.removeWordFromString(SMSCONFIRM3, ConfigApp.SMS_THRESHOLD);
        assertEquals("ANDROIDID=589  R01: Le seuil de 1 pour la maladie meningite a été atteint (3) à partir du 24/11/2014", sms);
        sms = HelperSms.removeAndroidIdFromString(sms);
        assertEquals(" R01: Le seuil de 1 pour la maladie meningite a été atteint (3) à partir du 24/11/2014", sms);
    }

}
