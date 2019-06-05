package org.argus.gateway.SmsSendManager;

import android.app.AlarmManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Build;
import android.os.SystemClock;

import org.argus.gateway.App;

import java.util.Date;

public abstract class QueuedMessage 
{
    protected long nextRetryTime = 0;
    protected int numRetries = 0;
    protected Date dateCreated = new Date();
    public App app;

    private int QUEUE_FIRST_RETRY = (Build.VERSION.SDK_INT >= 21) ? 60 : 20; // Impossible to have an Alarm below 60 sec with Android Lolipop and next
    
    protected long persistedId = 0; // _id of row in pending_incoming_messages or pending_outgoing_messages table (0 if not stored)

    public QueuedMessage(App app)
    {
        this.app = app;
    }
    
    public boolean isPersisted()
    {
        return persistedId != 0;
    }
    
    public long getPersistedId()
    {
        return persistedId;
    }
    
    public void setPersistedId(long id)
    {
        this.persistedId = id;
    }        
    
    public Date getDateCreated()
    {
        return dateCreated;
    }
    
    public int getNumRetries()
    {
        return numRetries;
    }
    
    public boolean canRetryNow() {
        return (nextRetryTime > 0 && nextRetryTime < SystemClock.elapsedRealtime());
    }

    /**
     *
     * @param numberOfConfigRetries - if == 0 , never give up
     *                              Number of retries after the 5 automatic mandatory retries
     *
     * @return
     */
    public boolean scheduleRetry(int numberOfConfigRetries) {
        long now = SystemClock.elapsedRealtime();
        numRetries++;

        int second = 1000;
        int minute = second * 60;

        app.log("failure " + numRetries);

        if (numRetries == 1) {
            app.log("retry in " + this.QUEUE_FIRST_RETRY + " seconds");
            nextRetryTime = now + this.QUEUE_FIRST_RETRY * second;
        } else if (numRetries == 2) {
            app.log("retry in 5 minutes");
            nextRetryTime = now + 5 * minute;
            //nextRetryTime = now + this.QUEUE_FIRST_RETRY * second;
        } else if (numRetries == 3) {
            app.log("retry in 15 minutes");
            nextRetryTime = now + 15 * minute;
            //nextRetryTime = now + this.QUEUE_FIRST_RETRY * second;
        } else if (numRetries == 4) {
            app.log("retry in 1 hour");
            nextRetryTime = now + 60 * minute;
            //nextRetryTime = now + this.QUEUE_FIRST_RETRY * second;
        } else if (numRetries == 5
                || numberOfConfigRetries == 0 // never give up
                || numRetries <= (numberOfConfigRetries + 5)) {
            app.log("retry in 12 hours");
            nextRetryTime = now + 12 * 60 * minute;
            //nextRetryTime = now + this.QUEUE_FIRST_RETRY * second;
        } else {
            app.log("giving up : numberOfConfigRetries = " + numberOfConfigRetries);
            return false;
        }

        AlarmManager alarm = (AlarmManager) app.getSystemService(Context.ALARM_SERVICE);

        PendingIntent pendingIntent = PendingIntent.getBroadcast(app,
                0,
                getRetryIntent(),
                0);

        if (Build.VERSION.SDK_INT >= 19) {
            alarm.setExact(AlarmManager.ELAPSED_REALTIME_WAKEUP, nextRetryTime, pendingIntent);
        } else {
            alarm.set(AlarmManager.ELAPSED_REALTIME_WAKEUP, nextRetryTime, pendingIntent);
        }

        return true;
    }
        
    public abstract String getDisplayType();    
    public abstract String getDescription();
    public abstract String getStatusText();
    
    public abstract Uri getUri();

    protected abstract Intent getRetryIntent();
}
