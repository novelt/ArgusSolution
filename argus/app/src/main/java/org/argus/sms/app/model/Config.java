package org.argus.sms.app.model;

import android.content.Context;
import android.database.Cursor;
import android.util.Log;

import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.HelperPreference;

/**
 * Storage of the configuration data. Stored as a Singleton in the application
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class Config {
    private final static String TAG = Config.class.getSimpleName();
    private final static boolean DEBUG = true;

    public final static int     MIN_DAY_IN_FIRST_WEEK = 4;

    public final static String KEYWORD_DISEASE = "DISEASE";
    public final static String KEYWORD_WEEK = "WEEK";
    public final static String KEYWORD_MONTH = "MONTH";
    public final static String KEYWORD_YEAR = "YEAR";
    public final static String KEYWORD_WEEKLY = "WEEKLY";
    public final static String KEYWORD_MONTHLY = "MONTHLY";
    public final static String KEYWORD_ALERT = "ALERT";
    public final static String KEYWORD_ID = "ANDROIDID";
    public final static String KEYWORD_LABEL = "LBL";
    public final static String KEYWORD_REPORT_ID = "RID";


    // Variable used when the test checkbox in settings is checked.
    // This will add the TEST_SUFFIX to all sms type
    public static boolean       IsTest = false;
    public static final String  TEST_SUFFIX = "-TEST";

    public final static String[] KEYS_DISEASE = {"disease","DISEASE","maladie","MALADIE","DIS"};
    public final static String[] KEYS_YEAR = {"year","YEAR","annee","ANNEE","YR"};
    public final static String[] KEYS_MONTH = {"month","MONTH","mois","MOIS","MOTH","MH"};
    public final static String[] KEYS_WEEK = {"week","WEEK","semaine","SEMAINE","WK"};
    public final static String[] KEYS_LABEL = {"LABEL","LBL","label","lbl"};

    private static Set<String> mAuthorizedkeys;

    private static HashMap<String,String> mBinding;

    /**
     * Init of all authorized keys
     */
    {
        mAuthorizedkeys = new HashSet<String>();
        mAuthorizedkeys.add(KEYWORD_DISEASE);
        mAuthorizedkeys.add(KEYWORD_WEEK);
        mAuthorizedkeys.add(KEYWORD_MONTH);
        mAuthorizedkeys.add(KEYWORD_YEAR);
        mAuthorizedkeys.add(KEYWORD_WEEKLY);
        mAuthorizedkeys.add(KEYWORD_MONTHLY);
        mAuthorizedkeys.add(KEYWORD_ALERT);
        mAuthorizedkeys.add(KEYWORD_ID);
        mAuthorizedkeys.add(KEYWORD_LABEL);
    }

    private static Config mInstance;

    /**
     * Constructor of the Config
     */
    private Config(){
        mBinding = new HashMap<String, String>();
    }

    /**
     * Get the instance of the Config. The Config is the Singleton.
     * @param ctx Contect of the application
     * @return the unique instance of Config
     */
    public static Config getInstance(Context ctx){
        if (mInstance == null){
           synchronized (Config.class){
               if (mInstance == null){
                   mInstance = new Config();
               }
           }
        }
        return mInstance;
    }

    /**
     * Get the value for a specified key
     * @param key key to get
     * @return the associated value
     */
    public String getValueForKey(String key){
        return mBinding.get(key);
    }

    /**
     * Get the key for a specified value
     * @param value value to get
     * @return the associated value
     */
    public String getKeyForValue(String value){
        if(mBinding.containsValue(value)){
            return getKeyByValue(mBinding, value);
        }
        return null;
    }

    /**
     * Add a pair (key/value) in the map if the key is authorized
     * @param key key to add
     * @param value value to add
     * @throws IllegalArgumentException if key not valid
     */
    public  void addKeyAndValue(String key, String value){
        if (mAuthorizedkeys.contains(key)) {
            mBinding.put(key,value);
        } else {
            throw new IllegalArgumentException("This key is not valid");
        }
    }

    /**
     * Return the Key for a specified Values (generic version)
     * @param map map to look into
     * @param value value to search
     * @return The key
     */
    private static <T, E> T getKeyByValue(Map<T, E> map, E value) {
        for (Map.Entry<T, E> entry : map.entrySet()) {
            if (value.equals(entry.getValue())) {
                return entry.getKey();
            }
        }
        return null;
    }

    /**
     * Remove all data in the binding map
     */
    public void clearData(){
        mBinding.clear();
    }

    /**
     * Load config data from a cursor
     * @param cursor cursor binded to the config data in database
     * @ctx the application context
     */
    public void loadDataFromCursor(final Cursor cursor, Context ctx) {
        if (cursor.moveToFirst()){
            do{
                String textSms = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));
                loadConfigForSms(textSms, ctx);
            }while(cursor.moveToNext());
        }
        // add the id
        addKeyAndValue(KEYWORD_ID, KEYWORD_ID);
    }

    /**
     * Load config into the map from sms string
     * @param textSms config sms text
     * @ctx the application context
     */
    public void loadConfigForSms(String textSms, Context ctx) {
        textSms = HelperSms.removeAndroidIdFromString(textSms);
        if (textSms.contains(Parser.TEMPLATE_ALERT)){
            parseConfigAlert(textSms);
        }else if (textSms.contains(Parser.TEMPLATE_WEEKLY)){
            parseConfigWeekly(textSms);
        }else if (textSms.contains(Parser.TEMPLATE_MONTHLY)){
            parseConfigMonthly(textSms);
        }else if (textSms.contains(Parser.TEMPLATE_CONF)){
            parseConfigHealthFacility(textSms, ctx);
        }
    }

    /**
     * Parse and save the health facility sms
     * @param textSms sms content
     * @param ctx application context
     */
    private void parseConfigHealthFacility(String textSms, Context ctx) {
        if (textSms.contains(Parser.TEMPLATE_HEALTHFACILITY)) {
            int start = textSms.indexOf(Parser.TEMPLATE_HEALTHFACILITY) + Parser.TEMPLATE_HEALTHFACILITY.length() + 1;
            String hf = textSms.substring(start, textSms.length() - 1);
            try {
                hf = hf.substring(0, hf.indexOf(Parser.SEPARATOR_COMMA));
            } catch (Exception e) {
                e.printStackTrace();
            }
            HelperPreference.setHFacility(ctx, hf);
        }
    }

    /**
     * Parse and analyse the Alert config sms
     * @param textSms alert config message text
     */
    private void parseConfigAlert(String textSms) {
        textSms = HelperSms.removeAndroidIdFromString(textSms);
        textSms = textSms.replace(Parser.TEMPLATE_ALERT+" ","");
        textSms.replace(Parser.SEPARATOR_FIELDS,Parser.SEPARATOR_COMMA);
        String[] tab = textSms.split(" ");
        if (tab.length>=2){
            // set the keyword for alert
            addKeyAndValue(KEYWORD_ALERT,tab[0]);
        }else{
            if (BuildConfig.ERROR){
             Log.e(TAG, "parseConfigAlert not enough parts");
            }
        }
    }

    /**
     * Parse and analyse the Weekly config sms
     * @param textSms Weekly config message text
     */
    private String parseConfigWeekly(String textSms) {
        return parseSmsForConfig(textSms,Parser.TEMPLATE_WEEKLY, KEYWORD_WEEKLY);
    }

    /**
     * Parse and analyse the Monthly config sms
     * @param textSms Monthly config message text
     */
    private String parseConfigMonthly(String textSms) {
        return parseSmsForConfig(textSms,Parser.TEMPLATE_MONTHLY, KEYWORD_MONTHLY);
    }


    /**
     * Parse the sms for a specific keyword and a specific config
     * @param textSms sms to parse
     * @param templateKeyword keyword to find
     * @param configKeyword configKeyword to find the find in the value
     * @return the disease if found
     */
    private String parseSmsForConfig(String textSms, String templateKeyword, String configKeyword){
        String disease = null;
        textSms = textSms.replace(templateKeyword+" ","");
        textSms = textSms.replace(Parser.SEPARATOR_FIELDS,Parser.SEPARATOR_COMMA);
        //String[] tab = textSms.split(" ");
        String[] tab = textSms.split(" ", 2); // Split only first instance of space car (because LBL can contains space cars)
        if (tab.length>=2){
            // set the keyword for templatekeyword
            addKeyAndValue(configKeyword,tab[0]);
            HashMap<String,String> values = Parser.parseFieldsForSeparator(tab[1],Parser.SEPARATOR_COMMA);
            String field;
            if ((field = Parser.findKeyForKeys(values,KEYS_DISEASE))!=null){
                addKeyAndValue(KEYWORD_DISEASE,field);
                disease = field;
            }
            if ((field = Parser.findKeyForKeys(values,KEYS_YEAR))!=null){
                addKeyAndValue(KEYWORD_YEAR,field);
            }
            if ((field = Parser.findKeyForKeys(values,KEYS_MONTH))!=null) {
                addKeyAndValue(KEYWORD_MONTH, field);
            }
            if ((field = Parser.findKeyForKeys(values,KEYS_WEEK))!=null) {
                addKeyAndValue(KEYWORD_WEEK, field);
            }
            if ((field = Parser.findKeyForKeys(values,KEYS_LABEL))!=null) {
                addKeyAndValue(KEYWORD_LABEL, field);
            }
        }else{
            if (BuildConfig.ERROR){
                Log.e(TAG, "parseConfigAlert not enough parts");
            }
        }
        return disease;
    }


    /**
     * Check if the config is loaded
     * @return true if the config is loaded, false otherwise
     */
    public boolean isConfigLoaded() {
        return mBinding.size()>0;
    }
}
