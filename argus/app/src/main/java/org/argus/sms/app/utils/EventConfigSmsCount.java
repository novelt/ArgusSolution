package org.argus.sms.app.utils;

/**
 * Event sent when the total SMS count message is received
 *
 * Event sent by Otto (http://square.github.io/otto/) on the event bus
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class EventConfigSmsCount {
    private final int mSmsCount;

    public EventConfigSmsCount(final int smsCount) {
        mSmsCount = smsCount;
    }

    /**
     * Get the SMS count
     * @return the sms count
     */
    public int getSmsCount() {
        return mSmsCount;
    }
}
