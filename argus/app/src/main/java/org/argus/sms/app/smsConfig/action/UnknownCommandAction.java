package org.argus.sms.app.smsConfig.action;

import android.content.Context;

import org.argus.sms.app.smsConfig.AckConfigSMS;
import org.argus.sms.app.smsConfig.OutgoingSMS;


/**
 * Created by eotin on 05/07/2017.
 */

public class UnknownCommandAction extends ConfigAction {

    @Override
    public OutgoingSMS execute(Context context)
    {
        OutgoingSMS resultSms ;

        resultSms = new AckConfigSMS(this.getGatewayNumber(context),
                this.getSmsId(),
                this.getImei(context),
                AckConfigSMS.ANSWER_CONFIG_ERROR_CODE.UNKNOWN_COMMAND);

        return resultSms ;
    }
}
