package org.argus.sms.app.utils;

/**
 * Event sent when the count of config sms has changed
 *
 * Event sent by Otto (http://square.github.io/otto/) on the event bus
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */

public class EventSyncMessageReceivedCount {
    private final int mSmsCount;

    public EventSyncMessageReceivedCount(final int smsCount) {
        mSmsCount = smsCount;
    }

    /**
     * return the number of sms
     * @return number of sync sms
     */
    public int getSmsCount() {
        return mSmsCount;
    }
}
