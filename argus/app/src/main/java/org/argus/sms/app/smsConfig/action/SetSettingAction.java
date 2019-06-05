package org.argus.sms.app.smsConfig.action;

import android.content.Context;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;

import org.argus.sms.app.smsConfig.AckConfigSMS;
import org.argus.sms.app.smsConfig.OutgoingSMS;
import org.argus.sms.app.smsConfig.security.BlackListSettings;

/**
 * Created by eotin on 27/06/2017.
 */

public class SetSettingAction extends ConfigAction {
    private final static String TAG = SetSettingAction.class.getSimpleName();

    private String value;

    public SetSettingAction(String setting, String value)
    {
        this.setting = setting;
        this.value = value;
    }

    @Override
    public OutgoingSMS execute(Context context)
    {
        AckConfigSMS resultSms = null ;

        if (this.getParameter() == null || this.getFixSetting().isEmpty()) {
            resultSms = new AckConfigSMS(this.getGatewayNumber(context),
                    this.getSmsId(),
                    this.getImei(context),
                    AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.UNKNOWN_SETTING);

            return resultSms ;
        }

        // We don't want to update a black listed setting
        if (BlackListSettings.getBlackListSettings(context).contains(this.getFixSetting().toLowerCase())) {
            resultSms = new AckConfigSMS(this.getGatewayNumber(context),
                    this.getSmsId(),
                    this.getImei(context),
                    AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.NON_EDITABLE_SETTING);

            return resultSms ;
        }

        if (this.value == null || this.value.isEmpty()) {
            resultSms = new AckConfigSMS(this.getGatewayNumber(context),
                    this.getSmsId(),
                    this.getImei(context),
                    AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.EMPTY_VALUE);

            return resultSms ;
        }

        SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(context.getApplicationContext());
        SharedPreferences.Editor editor = prefs.edit();

        if (this.getParameter() instanceof String) {
            editor.putString(this.getFixSetting(), String.valueOf(this.value));
            editor.commit();
        } else if(this.getParameter() instanceof Boolean) {
            editor.putBoolean(this.getFixSetting(), Boolean.valueOf(this.value));
            editor.commit();
        } else if(this.getParameter() instanceof Integer) {
            editor.putInt(this.getFixSetting(), Integer.valueOf(this.value));
            editor.commit();
        } else {
            return null ;
        }

        resultSms = new AckConfigSMS(this.getGatewayNumber(context),
                this.getSmsId(),
                this.getImei(context),
                AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.OK);

        return resultSms ;
    }
}
