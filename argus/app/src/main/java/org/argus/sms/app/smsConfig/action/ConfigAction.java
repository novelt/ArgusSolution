package org.argus.sms.app.smsConfig.action;

import android.content.Context;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;
import android.util.Log;

import org.argus.sms.app.smsConfig.OutgoingSMS;
import org.argus.sms.app.smsConfig.utility.ConfigSMSParser;
import org.argus.sms.app.utils.HelperPreference;

import java.util.Map;

/**
 * Created by eotin on 27/06/2017.
 */

public abstract class ConfigAction {
    private final static String TAG = ConfigAction.class.getSimpleName();

    private static final String CONFIG_SMS_CMD_UPDATE_SETTINGS = "SET";
    private static final String CONFIG_SMS_CMD_GET_SETTINGS = "GET";

    protected String setting;
    protected String fix_setting;
    protected int smsId;

    protected Object parameter;

    public static ConfigAction createInstance(ConfigSMSParser smsParser, Context context)
    {
        ConfigAction instance ;

        if (!smsParser.isConfigSmsValid(context)) {
            instance = new InvalidConfigAction(smsParser);
        } else {

            switch (smsParser.getCmd()) {
                case CONFIG_SMS_CMD_UPDATE_SETTINGS:
                    instance = new SetSettingAction(smsParser.getSetting(), smsParser.getValue());
                    break;
                case CONFIG_SMS_CMD_GET_SETTINGS:
                    instance = new GetSettingAction(smsParser.getSetting());
                    break;
                default:
                    instance = new UnknownCommandAction();
                    Log.e(TAG, "createInstance: unknown command");
            }
        }

        instance.findParameter(context);
        instance.smsId = smsParser.getId();

        return instance;
    }

    private void findParameter(Context context)
    {
        if (this.setting == null || this.setting.isEmpty()) {
            Log.w(TAG, String.format("Setting is null or empty"));
            return ;
        }

        SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(context.getApplicationContext());
        Map<String, ?> all = prefs.getAll();

        if (all.containsKey(this.setting)) {
            this.parameter = all.get(this.setting);
            this.fix_setting = this.setting;
        }

        if (parameter == null) {
            // Try to find the setting with toUpper()
            for (String key:all.keySet()) {
                if (key.toUpperCase().equals(this.setting.toUpperCase())) {
                    this.fix_setting = key;
                    this.parameter = all.get(key);
                    break ;
                }
            }
        }

        if (parameter == null) {
            // Try to find the setting with Contains()
            for (String key:all.keySet()) {
                if (key.toUpperCase().contains(this.setting.toUpperCase())) {
                    this.fix_setting = key;
                    this.parameter = all.get(key);
                    break ;
                }
            }
        }

        if (parameter == null) {
            Log.w(TAG, String.format("Parameter {0} not found in settings", this.setting));
        }
    }

    protected Object getParameter()
    {
        return this.parameter;
    }

    protected String getFixSetting()
    {
        return this.fix_setting;
    }

    protected int getSmsId()
    {
        return this.smsId;
    }

    protected String getImei(Context context)
    {
        return HelperPreference.getUniqueDeviceNumber(context);
    }

    protected String getGatewayNumber(Context context)
    {
        return HelperPreference.getServerPhoneNumber(context);
    }

    public abstract OutgoingSMS execute(Context context);
}
