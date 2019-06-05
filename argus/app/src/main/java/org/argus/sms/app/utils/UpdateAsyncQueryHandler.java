package org.argus.sms.app.utils;

import android.content.AsyncQueryHandler;
import android.content.ContentResolver;
import android.util.Log;

import org.argus.sms.app.BuildConfig;

/**
 * Class to update the database asynchronously
 */
public class UpdateAsyncQueryHandler extends AsyncQueryHandler {
private final static String TAG = UpdateAsyncQueryHandler.class.getSimpleName();
private final static boolean DEBUG = true;

        public UpdateAsyncQueryHandler(final ContentResolver cr) {
            super(cr);
        }

        @Override
        protected void onUpdateComplete(final int token, final Object cookie, final int result) {
            super.onUpdateComplete(token, cookie, result);
            if (BuildConfig.DEBUG && DEBUG){
                Log.d(TAG, "onUpdateComplete result=" + result);
            }
        }
    }