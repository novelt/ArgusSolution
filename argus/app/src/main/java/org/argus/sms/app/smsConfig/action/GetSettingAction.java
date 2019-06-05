package org.argus.sms.app.smsConfig.action;

import android.content.Context;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;

import org.argus.sms.app.smsConfig.AckConfigSMS;
import org.argus.sms.app.smsConfig.AnswerConfigSMS;
import org.argus.sms.app.smsConfig.OutgoingSMS;
import org.argus.sms.app.utils.HelperPreference;

/**
 * Created by eotin on 27/06/2017.
 *
 * Class managing get Setting Action
 */
public class GetSettingAction extends ConfigAction {

    private final String CONFIG_SMS_SETTING_IMEI = "IMEI";

    public GetSettingAction(String setting)
    {
        this.setting = setting;
    }

    @Override
    public OutgoingSMS execute(Context context)
    {
        OutgoingSMS resultSms ;

        if (this.getParameter() == null || this.getFixSetting().isEmpty()) {
            resultSms = new AckConfigSMS(this.getGatewayNumber(context),
                    this.getSmsId(),
                    this.getImei(context),
                    AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.UNKNOWN_SETTING);

            return resultSms ;
        }

        String value = getSettingValue(context);

        if (value == null) {
            resultSms = new AckConfigSMS(this.getGatewayNumber(context),
                    this.getSmsId(),
                    this.getImei(context),
                    AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.UNKNOWN_SETTING);
        } else {
            resultSms = new AnswerConfigSMS(this.getGatewayNumber(context), this.getSmsId(), this.getImei(context), value);
        }

        return resultSms ;
    }

    private String getSettingValue(Context context)
    {
        // If IMEI is requested, by pass all search in settings
        if (this.setting.trim().toUpperCase().equals(CONFIG_SMS_SETTING_IMEI)) {
            return HelperPreference.getUniqueDeviceNumber(context);

        } else {
            if (this.getParameter() == null || this.getFixSetting().isEmpty()) {
                return null;
            }
            SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(context.getApplicationContext());

            if (this.getParameter() instanceof String) {
                return prefs.getString(this.getFixSetting(), null);
            } else if (this.getParameter() instanceof Boolean) {
                return Boolean.toString(prefs.getBoolean(this.getFixSetting(), false));
            } else if (this.getParameter() instanceof Integer) {
                return Integer.toString(prefs.getInt(this.getFixSetting(), -1));
            }
        }

        return null ;
    }
}
