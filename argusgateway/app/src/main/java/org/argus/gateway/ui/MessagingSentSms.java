package org.argus.gateway.ui;

import android.content.Context;
import android.database.Cursor;
import android.net.Uri;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CursorAdapter;
import android.widget.TextView;

import org.argus.gateway.IncomingMessage;
import org.argus.gateway.IncomingSms;
import org.argus.gateway.R;

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;

public class MessagingSentSms extends MessagingForwarder {

    private Cursor cur;

    public IncomingMessage getMessageAtPosition(int position)
    {
        int addressIndex = cur.getColumnIndex("address");
        int bodyIndex = cur.getColumnIndex("body");
        int dateIndex = cur.getColumnIndex("date");

        cur.moveToPosition(position);
        
        IncomingSms sms = new IncomingSms(app);
        
        sms.setDirection(IncomingMessage.Direction.Sent);
        sms.setTo(cur.getString(addressIndex));
        sms.setTimestamp(cur.getLong(dateIndex));
        sms.setMessageBody(cur.getString(bodyIndex));
        
        return sms;
    }

    // first time the Menu key is pressed
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        MenuInflater inflater = getMenuInflater();
        inflater.inflate(R.menu.sent_sms, menu);
        return(true);
    }

    @Override
    public boolean onPrepareOptionsMenu(Menu menu) {
        return true;
    }

    public int getMessageCount()
    {
        return cur.getCount();
    }
    
    public void initListAdapter() {
        // undocumented API; see
        // core/java/android/provider/Telephony.java

        Uri inboxUri = Uri.parse("content://sms/sent");

        cur = getContentResolver().query(inboxUri,
                new String[]{"_id", "address", "body", "date"}, null, null,
                "_id desc limit 50");

        final LayoutInflater inflater = (LayoutInflater)getSystemService(LAYOUT_INFLATER_SERVICE);
        final DateFormat dateFormat = new SimpleDateFormat("dd MMM HH:mm:ss");
        
        CursorAdapter adapter = new CursorAdapter(this, cur) {                    
             public View newView(Context context, Cursor cursor, ViewGroup parent) {
                return inflater.inflate(R.layout.inbox_item, null);
             }
             
             public void bindView(View view, Context context, Cursor cursor)
             {
                TextView addrText = (TextView) view.findViewById(R.id.inbox_address);
                TextView bodyText = (TextView) view.findViewById(R.id.inbox_body);
                    
                String address = cursor.getString(1);
                String body = cursor.getString(2);
                long date = cursor.getLong(3);
                
                addrText.setText(address + " (" + dateFormat.format(new Date(date)) + ")");
                bodyText.setText(body);
             }
        };

        setListAdapter(adapter);
    }
}
