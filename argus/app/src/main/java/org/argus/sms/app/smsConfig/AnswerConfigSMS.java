package org.argus.sms.app.smsConfig;

import java.security.InvalidParameterException;

/**
 * Created by eotin on 04/07/2017.
 */

public class AnswerConfigSMS extends OutgoingSMS {

    public AnswerConfigSMS(String to, int smsId, String imei, String value) throws InvalidParameterException
    {
        super(to, smsId, imei);

        this.setMessages(value);

       /* this.message = String.format(Locale.US, "%1$s%2$s%3$s%2$s%4$d%2$s%5$s",
                ConfigSMSParser.CONFIG_ACK_SMS_KEYWORD,
                ConfigSMSParser.CONFIG_SMS_SEPARATOR,
                this.imei,
                this.smsId,
                this.value
        );*/
    }
}
