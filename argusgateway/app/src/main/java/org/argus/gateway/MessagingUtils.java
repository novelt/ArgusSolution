
package org.argus.gateway;

import android.content.ContentResolver;
import android.database.Cursor;
import android.net.Uri;

import org.argus.gateway.SmsSendManager.OutgoingMessage;

import java.util.*;

/*
 * Utilities for parsing MMS and SMS messages from the content provider tables 
 * of the Messaging app, as defined by android.provider.Telephony
 * 
 * MMS parsing is analogous to com.google.android.mms.pdu.PduPersister from 
 * core/java/com/google/android/mms/pdu in the base Android framework
 * (https://github.com/android/platform_frameworks_base)
 */
public class MessagingUtils
{
    // constants from android.provider.Telephony
    public static final Uri MMS_INBOX_URI = Uri.parse("content://mms/inbox");        
    public static final Uri MMS_PART_URI = Uri.parse("content://mms/part");    
    
    public static final Uri SENT_SMS_URI = Uri.parse("content://sms/sent");
    
    // constants from com.google.android.mms.pdu.PduHeaders  
    private static final int PDU_HEADER_FROM = 0x89;    
    private static final int MESSAGE_TYPE_RETRIEVE_CONF = 0x84;
    
    // todo -- prevent (very slow) unbounded growth?
    private final Set<Long> seenMmsIds = new HashSet<Long>();
    
    private final Set<Long> seenSentSmsIds = new HashSet<Long>();
    
    private App app;
    private ContentResolver contentResolver;
    
    public MessagingUtils(App app)
    {
        this.app = app;
        this.contentResolver = app.getContentResolver();
    }
    
    public List<MmsPart> getMmsParts(long id)
    {
        Cursor cur = null;

        // assume that if there is at least one part saved in database
        // then MMS is fully delivered (this seems to be true in practice)
        List<MmsPart> parts = new ArrayList<MmsPart>();

        try {
             cur = contentResolver.query(MMS_PART_URI, new String[]{
                    "_id", "ct", "name", "text", "cid", "_data"
            }, "mid = ?", new String[]{"" + id}, null);

            if (cur == null) {
                return parts;
            }

            while (cur.moveToNext()) {
                long partId = cur.getLong(0);

                String contentType = cur.getString(1);

                if (contentType == null) {
                    continue;
                }

                MmsPart part = new MmsPart(app, partId);

                part.setContentType(contentType);

                String name = cur.getString(2);

                if (name == null || name.length() == 0) {
                    // POST request for incoming MMS will fail if the filename is empty
                    name = UUID.randomUUID().toString().substring(0, 8);
                }

                part.setName(name);
                part.setDataFile(cur.getString(5));
                // todo interpret charset like com.google.android.mms.pdu.EncodedStringValue
                part.setText(cur.getString(3));
                part.setContentId(cur.getString(4));
                parts.add(part);
            }
        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            if (cur != null) {
                cur.close();
            }
        }

        return parts;
    }
    
    /*
     * see com.google.android.mms.pdu.PduPersister.loadAddress
     */
    public String getMmsSenderNumber(long mmsId)
    {
        Cursor cur = null ;
        String address = null;

        try {
            Uri uri = Uri.parse("content://mms/" + mmsId + "/addr");

            cur = contentResolver.query(uri,
                    new String[]{"address", "charset", "type"},
                    null, null, null);

            if (cur == null) {
                return address ;
            }

            while (cur.moveToNext()) {
                int addrType = cur.getInt(2);
                if (addrType == PDU_HEADER_FROM) {
                    // todo interpret charset like com.google.android.mms.pdu.EncodedStringValue
                    address = cur.getString(0);
                }
            }
            cur.close();
        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            if (cur != null) {
                cur.close();
            }
        }

        return address;
    }
    
    public List<IncomingMms> getMessagesInMmsInbox()
    {
        return getMessagesInMmsInbox(false);
    }

    public synchronized List<IncomingMms> getMessagesInMmsInbox(boolean newMessagesOnly)
    {
        Cursor c = null ;
        List<IncomingMms> messages = new ArrayList<IncomingMms>();

        try {
            // the M-Retrieve.conf messages are the 'actual' MMS messages
            String m_type = "" + MESSAGE_TYPE_RETRIEVE_CONF;

            c = contentResolver.query(MMS_INBOX_URI,
                    new String[]{"_id", "ct_l", "date"},
                    "m_type = ? ", new String[]{m_type},
                    "_id desc limit 30");

            if (c == null) {
                return messages ;
            }

            while (c.moveToNext()) {
                long id = c.getLong(0);

                if (newMessagesOnly && seenMmsIds.contains(id)) {
                    // avoid fetching all the info for old MMS messages if we're only interested in new ones
                    continue;
                }

                long date = c.getLong(2);

                IncomingMms mms = new IncomingMms(app,
                        date * 1000, // MMS timestamp is in seconds for some reason,
                        // while everything else is in ms
                        id);

                messages.add(mms);
            }

        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            if (c != null) {
                c.close();
            }
        }
        
        return messages;
    }
    
    public synchronized boolean deleteFromMmsInbox(IncomingMms mms)
    {        
        long id = mms.getMessagingId();
                
        Uri uri = Uri.parse("content://mms/inbox/" + id);            
        int res = contentResolver.delete(uri, null, null);
                
        if (res > 0)
        {
            app.log("MMS id="+id+" deleted from inbox");
            
            // remove id from set because Messaging app reuses ids 
            // of deleted messages.             
            // TODO: handle reuse of IDs deleted directly through Messaging
            // app while ArgusGateway is running
            seenMmsIds.remove(id);
        }
        else
        {
            app.log("MMS id="+id+" could not be deleted from inbox");
        }
        return res  > 0;
    }

    /**
     * Function use to delete message from device sms box.
     * @param sms the inbox sms to delete
     * @return true if success and false in other case
     */
    public synchronized boolean deleteFromSmsInbox(IncomingMessage sms)
    {
        Cursor c = null;

        try {
            Uri uriSms = Uri.parse("content://sms");
            c = app.getContentResolver().query(uriSms,
                    new String[] { "_id", "thread_id", "address",
                            "person", "date", "body" }, null, null, null);

            if (c != null && c.moveToFirst()) {
                do {
                    long id = c.getLong(0);
                    //long threadId = c.getLong(1);
                    //String address = c.getString(2);
                    String body = c.getString(5);
                    String date = c.getString(4);
                    Long smsDate = sms.timestamp;

                    if (sms.getMessageBody().equals(body) && date.equals(smsDate.toString())) {
                        app.getContentResolver().delete(
                                Uri.parse("content://sms/" + id), null, null);
                        app.log("SMS id=" + id + " deleted from inbox");
                    }
                } while (c.moveToNext());
            }
        } catch (Exception e) {
            e.printStackTrace();

            app.log("SMS could not be deleted from inbox");
        } finally {
            if (c != null) {
                c.close();
            }
        }

        return true;
    }

    public synchronized void markSeenMms(IncomingMms mms)
    {
        long id = mms.getMessagingId();        
        seenMmsIds.add(id);
    }
    
    public synchronized List<IncomingSms> getSentSmsMessages()
    {
        return getSentSmsMessages(false);
    }
    
    public synchronized List<IncomingSms> getSentSmsMessages(boolean newMessagesOnly)
    {
        Cursor c = null ;
        // SMS messages sent via Messaging app are considered as IncomingSms (with direction=Direction.Sent)
        // because they're incoming to the server (whereas OutgoingSms would indicate a message we will try to send)
        List<IncomingSms> messages = new ArrayList<IncomingSms>();

        try {
            c = contentResolver.query(SENT_SMS_URI,
                    new String[]{"_id", "address", "body", "date"}, null, null,
                    "_id desc limit 30");

            if (c == null) {
                return messages;
            }

            while (c.moveToNext()) {
                long id = c.getLong(0);

                if (newMessagesOnly && seenSentSmsIds.contains(id)) {
                    continue;
                }

                IncomingSms sms = new IncomingSms(app);
                sms.setMessagingId(id);
                sms.setTo(c.getString(1));
                sms.setMessageBody(c.getString(2));
                sms.setTimestamp(c.getLong(3));
                sms.setDirection(IncomingSms.Direction.Sent);

                messages.add(sms);
            }

        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            if (c != null) {
                c.close();
            }
        }
        
        return messages;
    }
    
    public synchronized void markSeenSentSms(IncomingSms sms)
    {
        long id = sms.getMessagingId();
        seenSentSmsIds.add(id);
    }    
}        
