package org.argus.sms.app.smsConfig.action;

import android.content.Context;

import org.argus.sms.app.smsConfig.ExceptionConfigSMS;
import org.argus.sms.app.smsConfig.OutgoingSMS;

/**
 * Created by eotin on 05/07/2017.
 */

public class ExceptionAction extends ConfigAction {

    private Exception ex ;

    public ExceptionAction (Exception ex)
    {
        this.ex = ex;
    }

    @Override
    public OutgoingSMS execute(Context context)
    {
        OutgoingSMS resultSms ;

        resultSms = new ExceptionConfigSMS(this.getGatewayNumber(context),
                this.getSmsId(),
                this.getImei(context),
                this.ex);

        return resultSms ;
    }
}
