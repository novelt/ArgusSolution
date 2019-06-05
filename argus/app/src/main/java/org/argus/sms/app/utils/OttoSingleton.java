package org.argus.sms.app.utils;

import com.squareup.otto.Bus;

/**
 * Otto bus singleton
 *
 * Created by ogoutet on 11/13/14.
 */
public class OttoSingleton {
    private static OttoSingleton ourInstance = new OttoSingleton();
    private final Bus mBus;

    public static OttoSingleton getInstance() {
        return ourInstance;
    }

    private OttoSingleton() {
        mBus = new Bus();
    }

    public Bus getBus() {
        return mBus;
    }
}
