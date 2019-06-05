package org.argus.sms.app.smsConfig.action;

import android.content.Context;

import org.argus.sms.app.smsConfig.AckConfigSMS;
import org.argus.sms.app.smsConfig.OutgoingSMS;
import org.argus.sms.app.smsConfig.utility.ConfigSMSParser;

/**
 * Created by eotin on 05/07/2017.
 */

public class InvalidConfigAction extends ConfigAction {

    private ConfigSMSParser smsParser;

    public InvalidConfigAction(ConfigSMSParser smsParser)
    {
        this.smsParser = smsParser;
    }

    @Override
    public OutgoingSMS execute(Context context)
    {
        OutgoingSMS resultSms ;

        AckConfigSMS.ANSWER_CONFIG_ERROR_CODE errorCode;

        if (!this.smsParser.isImeiValid(context)) {
            errorCode = AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.WRONG_IMEI;
        } else if (!this.smsParser.isHashValid()) {
            errorCode = AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.WRONG_HASH;
        }
        else {
            errorCode = AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.OTHER;
        }

        resultSms = new AckConfigSMS(this.getGatewayNumber(context),
                this.getSmsId(),
                this.getImei(context),
                errorCode);

        return resultSms ;
    }
}
