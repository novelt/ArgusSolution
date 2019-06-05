package org.argus.sms.app.smsConfig;

import java.security.InvalidParameterException;

/**
 * Created by eotin on 04/07/2017.
 */

public class AckConfigSMS extends OutgoingSMS {

    private ANSWER_CONFIG_ERROR_CODE errorCode;

    public AckConfigSMS(String to, int smsId, String imei, ANSWER_CONFIG_ERROR_CODE errorCode) throws InvalidParameterException
    {
        super(to, smsId, imei);

        this.errorCode = errorCode;

        this.setMessages(this.errorCode.getMessage(), String.valueOf(this.errorCode.getValue()));

        /*this.encryptMessage(String.format(Locale.US, "%1$s%2$s%3$s%2$s%4$d%2$s%5$s%2$s%6$d",
                ConfigSMSParser.CONFIG_ACK_SMS_KEYWORD,
                ConfigSMSParser.CONFIG_SMS_SEPARATOR,
                this.imei,
                this.smsId,
                this.errorCode.getMessage(),
                this.errorCode.getValue()
        ));*/
    }
}
