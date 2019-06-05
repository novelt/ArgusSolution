package org.argus.sms.app.smsConfig;

import java.security.InvalidParameterException;

/**
 * Created by eotin on 04/07/2017.
 */

public class ExceptionConfigSMS extends OutgoingSMS {

    public ExceptionConfigSMS(String to, int smsId, String imei, Exception ex) throws InvalidParameterException
    {
        super(to, smsId, imei);

        this.setMessages(ANSWER_CONFIG_ERROR_CODE.EXCEPTION.getMessage(),
                String.valueOf(ANSWER_CONFIG_ERROR_CODE.EXCEPTION.getValue()));

        int length = LENGTH_SMS_MAX - this.getMessage().length();

        this.setMessages(ANSWER_CONFIG_ERROR_CODE.EXCEPTION.getMessage(),
                String.valueOf(ANSWER_CONFIG_ERROR_CODE.EXCEPTION.getValue()),
                (ex.getMessage().length() >= length) ?  ex.getMessage().substring(0, length -1) : ex.getMessage().substring(0, ex.getMessage().length()));
    }
}
