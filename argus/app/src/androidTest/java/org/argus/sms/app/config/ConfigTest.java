package org.argus.sms.app.config;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 */

import android.database.Cursor;
import android.test.ProviderTestCase2;
import android.test.mock.MockContentResolver;

import java.util.List;

import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.provider.SesProvider;
import org.argus.sms.app.utils.HelperSms;


/**
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ConfigTest  extends ProviderTestCase2 {

    private final static String SMSCONFIG1 = "ANDROIDID=12 TEMPLATE-ALERT: ALERT EVENEMENT=String , DATE=String , LIEU=String , CAS=Integer , HOSPITALISES=Integer , DECES=Integer";
    private final static String SMSCONFIG1_ALT1 = "ANDROIDID=12 TEMPLATE-ALERT: alerte EVENEMENT=String , DATE=String , LIEU=String , CAS=Integer , HOSPITALISES=Integer , DECES=Integer";
    private final static String SMSCONFIG1_ALT2 = "TEMPLATE-ALERT: toto EVENEMENT=String , DATE=String , LIEU=String , CAS=Integer , HOSPITALISES=Integer , DECES=Integer";
    private final static String SMSCONFIG2 = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=COQUELUCHE , YEAR=Integer , WEEK=Integer , CASVACCINES=Integer , CASNONVACCINES=Integer";
    private final static String SMSCONFIG3 = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=DIPHTERIE , YEAR=Integer , WEEK=Integer , CASVACCINES=Integer , CASNONVACCINES=Integer";
    private final static String SMSCONFIG4 = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=HEPATITE , YEAR=Integer , WEEK=Integer , CAS=Integer";
    private final static String SMSCONFIG5 = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=LEPTOSPIROSE , YEAR=Integer , WEEK=Integer , CAS=Integer";
    private final static String SMSCONFIG6 = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=MENINGITE , YEAR=Integer , WEEK=Integer , CAS=Integer";
    private final static String SMSCONFIG7 = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=ROUGEOLE , YEAR=Integer , WEEK=Integer , CASVACCINES=Integer , CASNONVACCINES=Integer";
    private final static String SMSCONFIG8 = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=TIAC , YEAR=Integer , WEEK=Integer , EPISODES=Integer , CAS=Integer";
    private final static String SMSCONFIG9 = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=TYPHOIDE , YEAR=Integer , WEEK=Integer , CAS=Integer";
    private final static String SMSCONFIGWEEK_ALT1 = "ANDROIDID=12 TEMPLATE-WEEKLY: REPORT DISEASE=TYPHOIDE , annee=Integer , semaine=Integer , CAS=Integer";
    private final static String SMSCONFIGWEEK_ALT2 = "ANDROIDID=12 TEMPLATE-WEEKLY: rapporthebdo maladie=TYPHOIDE , annee=Integer , semaine=Integer , CAS=Integer";
    private final static String SMSCONFIG10 = "ANDROIDID=12 CONF: M4ConfAlert=The alert confirmation wasn't received, please contact our manager";
    private final static String SMSCONFIG11 = "ANDROIDID=12 CONF: M4ConfW=The week report confirmation wasn't received, please contact our manager";
    private final static String SMSCONFIG12 = "ANDROIDID=12 CONF: M4ConfM=The month report confirmation wasn't received, please contact our manager";
    private final static String SMSCONFIG13 = "ANDROIDID=12 CONF: NbMsg=14, NbCharMax=150, Server=+33674525427, WeekStart=1, D4ConfAlert=30, D4ConfW=120, D4ConfM=120";
    private final static String SMSCONFIG14 = "ANDROIDID=12 TEMPLATE-MONTHLY: REPORTMONTHLY DISEASE=TYPHOIDE , YEAR=Integer , MONTH=Integer , CAS=Integer";

    private MockContentResolver mContentResolver;

    public ConfigTest() {
        super(SesProvider.class, SesContract.CONTENT_AUTHORITY);
    }

    @Override
    protected void setUp() throws Exception {
        super.setUp();
        Config.getInstance(getContext()).clearData();
        mContentResolver = getMockContentResolver();
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG1);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG2);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG3);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG4);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG5);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG6);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG7);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG8);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG9);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG10);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG11);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG12);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG13);
        HelperSms.saveSmsConfigToDatabase(mContentResolver,SMSCONFIG14);
    }


   public void testConfigLoad(){
       String selection = SesContract.Sms.TYPE +"="+ TypeSms.MODEL.toInt();
       Cursor cursor =  mContentResolver.query(SesContract.Sms.CONTENT_URI,null,selection,null,null);
       assertEquals(10, cursor.getCount());
       Config config = Config.getInstance(getContext());
       config.loadDataFromCursor(cursor, mContext);

       assertEquals("ALERT", config.getValueForKey(Config.KEYWORD_ALERT));
       assertEquals("REPORT", config.getValueForKey(Config.KEYWORD_WEEKLY));
       assertEquals("REPORTMONTHLY", config.getValueForKey(Config.KEYWORD_MONTHLY));

       selection = SesContract.Sms.ID + "=?";
       String[] selectionArgs = {"12"};
       cursor =  mContentResolver.query(SesContract.Sms.CONTENT_URI,null,selection,selectionArgs,null);
       assertEquals(14, cursor.getCount());

   }

    public void testConfigAlert(){
        String selection = SesContract.Sms.TYPE +"="+ TypeSms.MODEL.toInt();
        Cursor cursor =  mContentResolver.query(SesContract.Sms.CONTENT_URI,null,selection,null,null);
        assertEquals(10, cursor.getCount());
        Config config = Config.getInstance(getContext());
        config.loadDataFromCursor(cursor, mContext);

         selection = SesContract.Sms.TYPE +"=? AND "+SesContract.Sms.SUBTYPE +"=?";
        String[] selectionArgs = {String.valueOf(TypeSms.MODEL.toInt()),String.valueOf(SubTypeSms.MODEL_ALERT.toInt())};
         cursor =  mContentResolver.query(SesContract.Sms.CONTENT_URI,null,selection,selectionArgs,null);
        assertEquals(1, cursor.getCount());
        assertTrue(cursor.moveToFirst());
        String sms = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));
        List<Parser.ConfigField> list = Parser.getOtherFieldsFromSms(sms,config);
        assertEquals(6,list.size());
        assertEquals("EVENEMENT",list.get(0).Name);
        assertEquals("String",list.get(0).Type);
        assertEquals("DATE",list.get(1).Name);
        assertEquals("String",list.get(1).Type);
        assertEquals("LIEU",list.get(2).Name);
        assertEquals("String",list.get(2).Type);

    }

    public void testConfigAlertFromSMS(){
        Config config = Config.getInstance(getContext());
        config.loadConfigForSms(SMSCONFIG1, mContext);
        assertEquals("ALERT", config.getValueForKey(Config.KEYWORD_ALERT));
        List<Parser.ConfigField> other = Parser.getOtherFieldsFromSms(SMSCONFIG1,config);
        assertEquals(6,other.size());
        assertEquals("EVENEMENT",other.get(0).Name);
        assertEquals("String",other.get(0).Type);
        assertEquals("DECES",other.get(5).Name);
        assertEquals("Integer",other.get(5).Type);
        // --------------------------------------------------------------------
        config.loadConfigForSms(SMSCONFIG1_ALT1, mContext);
        assertEquals("alerte", config.getValueForKey(Config.KEYWORD_ALERT));
        other = Parser.getOtherFieldsFromSms(SMSCONFIG1,config);
        assertEquals(6,other.size());
        assertEquals("EVENEMENT",other.get(0).Name);
        assertEquals("String",other.get(0).Type);
        assertEquals("DECES",other.get(5).Name);
        assertEquals("Integer",other.get(5).Type);
        // --------------------------------------------------------------------
        config.loadConfigForSms(SMSCONFIG1_ALT2, mContext);
        assertEquals("toto", config.getValueForKey(Config.KEYWORD_ALERT));
        other = Parser.getOtherFieldsFromSms(SMSCONFIG1,config);
        assertEquals(6,other.size());
        assertEquals("EVENEMENT",other.get(0).Name);
        assertEquals("String",other.get(0).Type);
        assertEquals("DECES",other.get(5).Name);
        assertEquals("Integer",other.get(5).Type);

        // Check not set values at null
        assertNull(config.getValueForKey(Config.KEYWORD_WEEKLY));
        assertNull(config.getValueForKey(Config.KEYWORD_MONTHLY));
        assertNull(config.getValueForKey(Config.KEYWORD_YEAR));
        assertNull(config.getValueForKey(Config.KEYWORD_MONTH));
        assertNull(config.getValueForKey(Config.KEYWORD_WEEK));
    }

    public void testConfigWeekFromSMS(){
        String smsConfig = SMSCONFIG2;
        Config config = Config.getInstance(getContext());
        config.loadConfigForSms(smsConfig, mContext);
        assertEquals("REPORT", config.getValueForKey(Config.KEYWORD_WEEKLY));
        assertEquals("DISEASE", config.getValueForKey(Config.KEYWORD_DISEASE));
        assertEquals("YEAR", config.getValueForKey(Config.KEYWORD_YEAR));
        assertEquals("WEEK", config.getValueForKey(Config.KEYWORD_WEEK));
        assertEquals("COQUELUCHE",Parser.getFieldFromSms(Config.KEYWORD_DISEASE,smsConfig,config));
        List<Parser.ConfigField> other = Parser.getOtherFieldsFromSms(smsConfig,config);
        assertEquals(2,other.size());
        assertEquals("CASVACCINES",other.get(0).Name);
        assertEquals("Integer",other.get(0).Type);
        assertEquals("CASNONVACCINES",other.get(1).Name);
        assertEquals("Integer",other.get(1).Type);
        assertNull(config.getValueForKey(Config.KEYWORD_MONTH));
        // --------------------------------------------------------------------
        smsConfig = SMSCONFIG3;
        config.loadConfigForSms(smsConfig, mContext);
        assertEquals("REPORT", config.getValueForKey(Config.KEYWORD_WEEKLY));
        assertEquals("DISEASE", config.getValueForKey(Config.KEYWORD_DISEASE));
        assertEquals("YEAR", config.getValueForKey(Config.KEYWORD_YEAR));
        assertEquals("WEEK", config.getValueForKey(Config.KEYWORD_WEEK));
        assertEquals("DIPHTERIE",Parser.getFieldFromSms(Config.KEYWORD_DISEASE,smsConfig,config));
        other = Parser.getOtherFieldsFromSms(smsConfig,config);
        assertEquals(2,other.size());
        assertEquals("CASVACCINES",other.get(0).Name);
        assertEquals("Integer",other.get(0).Type);
        assertEquals("CASNONVACCINES",other.get(1).Name);
        assertEquals("Integer",other.get(1).Type);
        assertNull(config.getValueForKey(Config.KEYWORD_MONTH));
        // --------------------------------------------------------------------
        smsConfig = SMSCONFIG5;
        config.loadConfigForSms(smsConfig, mContext);
        assertEquals("REPORT", config.getValueForKey(Config.KEYWORD_WEEKLY));
        assertEquals("DISEASE", config.getValueForKey(Config.KEYWORD_DISEASE));
        assertEquals("YEAR", config.getValueForKey(Config.KEYWORD_YEAR));
        assertEquals("WEEK", config.getValueForKey(Config.KEYWORD_WEEK));
        assertEquals("LEPTOSPIROSE",Parser.getFieldFromSms(Config.KEYWORD_DISEASE,smsConfig,config));
        other = Parser.getOtherFieldsFromSms(smsConfig,config);
        assertEquals(1,other.size());
        assertEquals("CAS",other.get(0).Name);
        assertEquals("Integer",other.get(0).Type);
        assertNull(config.getValueForKey(Config.KEYWORD_MONTH));
        // --------------------------------------------------------------------
        smsConfig = SMSCONFIGWEEK_ALT1;
        config.loadConfigForSms(smsConfig, mContext);
        assertEquals("REPORT", config.getValueForKey(Config.KEYWORD_WEEKLY));
        assertEquals("DISEASE", config.getValueForKey(Config.KEYWORD_DISEASE));
        assertEquals("annee", config.getValueForKey(Config.KEYWORD_YEAR));
        assertEquals("semaine", config.getValueForKey(Config.KEYWORD_WEEK));
        assertEquals("TYPHOIDE",Parser.getFieldFromSms(Config.KEYWORD_DISEASE,smsConfig,config));
        other = Parser.getOtherFieldsFromSms(smsConfig,config);
        assertEquals(1,other.size());
        assertEquals("CAS",other.get(0).Name);
        assertEquals("Integer",other.get(0).Type);
        assertNull(config.getValueForKey(Config.KEYWORD_MONTH));
        // --------------------------------------------------------------------
        smsConfig = SMSCONFIGWEEK_ALT2;
        config.loadConfigForSms(smsConfig, mContext);
        assertEquals("rapporthebdo", config.getValueForKey(Config.KEYWORD_WEEKLY));
        assertEquals("maladie", config.getValueForKey(Config.KEYWORD_DISEASE));
        assertEquals("annee", config.getValueForKey(Config.KEYWORD_YEAR));
        assertEquals("semaine", config.getValueForKey(Config.KEYWORD_WEEK));
        assertEquals("TYPHOIDE",Parser.getFieldFromSms(Config.KEYWORD_DISEASE,smsConfig,config));
        other = Parser.getOtherFieldsFromSms(smsConfig,config);
        assertEquals(1,other.size());
        assertEquals("CAS",other.get(0).Name);
        assertEquals("Integer",other.get(0).Type);
        assertNull(config.getValueForKey(Config.KEYWORD_MONTH));

    }

    public void testConfigMonthFromSMS(){
        String smsConfig = SMSCONFIG14;
        Config config = Config.getInstance(getContext());
        config.loadConfigForSms(smsConfig, mContext);
        assertEquals("REPORTMONTHLY", config.getValueForKey(Config.KEYWORD_MONTHLY));
        assertEquals("DISEASE", config.getValueForKey(Config.KEYWORD_DISEASE));
        assertEquals("YEAR", config.getValueForKey(Config.KEYWORD_YEAR));
        assertEquals("MONTH", config.getValueForKey(Config.KEYWORD_MONTH));
        assertEquals("TYPHOIDE",Parser.getFieldFromSms(Config.KEYWORD_DISEASE,smsConfig,config));
        List<Parser.ConfigField> other = Parser.getOtherFieldsFromSms(smsConfig,config);
        assertEquals(1,other.size());
        assertEquals("CAS",other.get(0).Name);
        assertEquals("Integer",other.get(0).Type);
        assertNull(config.getValueForKey(Config.KEYWORD_WEEK));
    }

    public void testConfigGetDisease(){
        assertNull(Parser.getDiseaseForSms(SMSCONFIG1));
        assertEquals("COQUELUCHE",Parser.getDiseaseForSms(SMSCONFIG2));
        assertEquals("DIPHTERIE",Parser.getDiseaseForSms(SMSCONFIG3));
        assertEquals("HEPATITE",Parser.getDiseaseForSms(SMSCONFIG4));
        assertEquals("LEPTOSPIROSE",Parser.getDiseaseForSms(SMSCONFIG5));
        assertEquals("MENINGITE",Parser.getDiseaseForSms(SMSCONFIG6));
        assertEquals("ROUGEOLE",Parser.getDiseaseForSms(SMSCONFIG7));
        assertEquals("TIAC",Parser.getDiseaseForSms(SMSCONFIG8));
        assertEquals("TYPHOIDE",Parser.getDiseaseForSms(SMSCONFIG9));
        assertEquals("TYPHOIDE",Parser.getDiseaseForSms(SMSCONFIGWEEK_ALT1));
        assertEquals("TYPHOIDE",Parser.getDiseaseForSms(SMSCONFIGWEEK_ALT2));
        assertNull(Parser.getDiseaseForSms(SMSCONFIG10));
        assertNull(Parser.getDiseaseForSms(SMSCONFIG11));
        assertEquals("TYPHOIDE",Parser.getDiseaseForSms(SMSCONFIG14));
    }

}

