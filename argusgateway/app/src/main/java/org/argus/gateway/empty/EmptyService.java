package org.argus.gateway.empty;

import android.app.Service;
import android.content.Intent;
import android.os.IBinder;

/**
 * Created by alexandre on 27/04/16.
 */
public class EmptyService extends Service {
    @Override
    public void onCreate() {

    }

    @Override
    public void onDestroy() {
    }

    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }
}
