package org.argus.sms.app.smsConfig;

import android.telephony.SmsMessage;

import java.security.InvalidParameterException;
import java.util.List;

/**
 * Created by eotin on 27/06/2017.
 */

public class IncomingSMS {

    private String message;
    private String from;

    public IncomingSMS(List<SmsMessage> smsParts) throws InvalidParameterException
    {
        this.from = smsParts.get(0).getOriginatingAddress();
        this.message = smsParts.get(0).getMessageBody();

        int numParts = smsParts.size();

        for (int i = 1; i < numParts; i++)
        {
            SmsMessage smsPart = smsParts.get(i);

            if (!smsPart.getOriginatingAddress().equals(from))
            {
                throw new InvalidParameterException(
                        "Tried to create IncomingSms from two different senders");
            }

            this.message = this.message + smsPart.getMessageBody();
        }
    }

    /**
     *
     * @param from
     * @param message
     *
     * @throws InvalidParameterException
     */
    public IncomingSMS(String from, String message) throws InvalidParameterException {
        this.from = from;
        this.message = message;
    }

    public String getMessage()
    {
        return this.message;
    }

    public String getFrom() {
        return this.from;
    }
}
