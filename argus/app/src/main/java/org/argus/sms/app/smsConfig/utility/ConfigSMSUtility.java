package org.argus.sms.app.smsConfig.utility;

import android.annotation.TargetApi;
import android.content.Context;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.net.Uri;
import android.os.Build;
import android.os.Environment;
import android.preference.PreferenceManager;
import android.provider.Telephony;
import android.text.TextUtils;
import android.util.Log;

import com.google.gson.Gson;
import com.squareup.tape.FileObjectQueue;
import com.squareup.tape.ObjectQueue;

import org.argus.sms.app.smsConfig.IncomingSMS;
import org.argus.sms.app.smsConfig.OutgoingSMS;
import org.argus.sms.app.smsConfig.action.ConfigAction;
import org.argus.sms.app.smsConfig.action.ExceptionAction;
import org.argus.sms.app.smsConfig.action.UnknownFormatAction;
import org.argus.sms.app.utils.HelperFile;
import org.argus.sms.app.utils.HelperPreference;
import org.argus.sms.app.utils.HelperSmsSender;

import java.io.ByteArrayInputStream;
import java.io.File;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.io.Reader;
import java.io.Writer;
import java.util.HashMap;

/**
 * Created by eotin on 03/07/2017.
 */

@TargetApi(19)
public class ConfigSMSUtility {
    private final static String TAG = ConfigSMSUtility.class.getSimpleName();

    private final static String TELEPHONY_TextBasedSmsColumns_BODY = (Build.VERSION.SDK_INT >= 19) ? Telephony.TextBasedSmsColumns.BODY : "body";
    private final static String TELEPHONY_TextBasedSmsColumns_ADDRESS = (Build.VERSION.SDK_INT >= 19) ? Telephony.TextBasedSmsColumns.ADDRESS : "address";

    private File file;
    private ObjectQueue<OutgoingSMS> queue;
    private HashMap<String, OutgoingSMS> pendingSmsList = new HashMap<>();

    private static ConfigSMSUtility singleton = null;

    public static ConfigSMSUtility getInstance(Context context)
    {
        if (singleton == null) {
            singleton = new ConfigSMSUtility();

            try {
                singleton.file = new File(HelperFile.CONFIG_SMS_QUEUE_PATH);
                singleton.queue = new FileObjectQueue<>(singleton.file,
                                                                    new GsonConverter(new Gson()));
            } catch (Exception ex) {
                Log.e(TAG, "Error when creating queue file Config sms", ex);

            } finally {

            }

        }

        return singleton;
    }

    public boolean isEnabled(Context context)
    {
        return HelperPreference.isConfigBySMSEnabled(context);
    }

    public boolean isOnlyDefinedGatewayEnabled(Context context)
    {
        return HelperPreference.isOnlyDefinedGatewayEnabled(context);
    }

    public void readPendingConfigSMS(final Context context)
    {
        if (this.getQueue() != null) {
            this.getQueue().setListener(new ObjectQueue.Listener<OutgoingSMS>() {
                @Override
                public void onAdd(ObjectQueue<OutgoingSMS> objectQueue, OutgoingSMS outgoingSMS) {

                }

                @Override
                public void onRemove(ObjectQueue<OutgoingSMS> objectQueue) {
                    sendPendingAckSMS(context);
                }
            });
        }

        Cursor cursor = null ;

        try {

            long lastSmsReadDate = HelperPreference.getLastSMSRead(context);

            cursor = context.getContentResolver().query(Uri.parse("content://sms/inbox"),
                    null,
                    Telephony.Sms.DATE + " > " + lastSmsReadDate,
                    null,
                    Telephony.Sms.DATE + " ASC ");

            if (cursor != null && cursor.moveToFirst()) { // must check the result to prevent exception
                do {
                    long smsDate;
                    String body;
                    String from;

                    smsDate = Long.valueOf(cursor.getString(cursor.getColumnIndexOrThrow(Telephony.Sms.DATE)));

                    Log.i(TAG, String.format("Reading message with date : %1$d", smsDate));

                    body = cursor.getString(cursor.getColumnIndexOrThrow(TELEPHONY_TextBasedSmsColumns_BODY));
                    from = cursor.getString(cursor.getColumnIndexOrThrow(TELEPHONY_TextBasedSmsColumns_ADDRESS));

                    Log.i(TAG, String.format("** Body : %1$s", body));
                    Log.i(TAG, String.format("** From : %1$s", from));

                    // try parse SMS
                    IncomingSMS incomingSMS = new IncomingSMS(from, body);
                    this.manageConfigSMS(incomingSMS, context);

                    // Save the Reception Date of the SMS to not parse it next time
                    HelperPreference.setLastSMSRead(context, smsDate);

                } while (cursor.moveToNext());
            }
        }
        catch(Exception ex)
        {
            Log.e(TAG, "An error occurs in readPendingConfigSMS function", ex);
        }
        finally {
            if (cursor != null && !cursor.isClosed()) {
                cursor.close();
            }
        }
    }

    private void manageConfigSMS(IncomingSMS incomingSMS, Context context)
    {
        OutgoingSMS sms = null;

        try {
            if (this.isOnlyDefinedGatewayEnabled(context)) {
                // Check Sender Number
                if (!HelperPreference.isPhoneNumberAValidServer(context, incomingSMS.getFrom().trim())) {
                    Log.i(TAG, String.format("Gateway number %1$s sending Config SMS not allowed", incomingSMS.getFrom().trim()));
                    return;
                }
            }

            if (!ConfigSMSParser.isConfigSMS(incomingSMS)) {
                Log.i(TAG, "SMS doesn't match Avadar config keyword");
                return;
            }

            ConfigSMSParser smsParser = ConfigSMSParser.parseSMS(incomingSMS, HelperPreference.getSecurityKey(context), true);

            if (smsParser != null) {
                ConfigAction action = ConfigAction.createInstance(smsParser, context);
                if (action != null) {
                    sms = action.execute(context);
                } else {
                    Log.i(TAG, "Avadar config action not recognized or not valid");
                }

            } else {
                Log.i(TAG, "Unresolved Config SMS content");
                sms = new UnknownFormatAction().execute(context);
            }
        }
        catch (Exception ex) {
            Log.e(TAG, "An error occurs in manageConfigSMS function", ex);
            sms = new ExceptionAction(ex).execute(context);
        }
        finally {
            if (sms != null && this.getQueue() != null) {
                this.getQueue().add(sms);
            }
        }
    }

    public void sendPendingAckSMS(final Context context)
    {
        try {
            OutgoingSMS sms = this.getQueue().peek();

            if (sms != null) {
                // Try Send SMS
                if (this.pendingSmsList.containsKey(String.valueOf(sms.getSmsId()))) {
                    return ;
                }

                this.pendingSmsList.put(String.valueOf(sms.getSmsId()), sms);

                if (TextUtils.isEmpty(sms.getTo())) {
                    Log.i(TAG, "To is not defined, impossible to send this message");
                    this.getQueue().remove();
                    return ;
                }

                HelperSmsSender.sendSms(context, sms.getTo(), sms.getEncryptMessage(), new HelperSmsSender.SmsListener<OutgoingSMS>() {
                    @Override
                    public void onSmsSentSuccess(String tag, OutgoingSMS sms) {
                        pendingSmsList.remove(String.valueOf(sms.getSmsId()));
                        getQueue().remove();
                    }

                    @Override
                    public void onSmsSentError(String tag, OutgoingSMS sms, int error) {
                        pendingSmsList.remove(String.valueOf(sms.getSmsId()));
                        getQueue().remove();
                        getQueue().add(sms);
                    }
                },
                "tag", sms);


            } else {
                Log.i(TAG, "No pending Config SMS to send");
            }
        }
        catch (Exception ex) {
            Log.e(TAG, "An error occurs in sendPendingAckSMS function", ex);
        }
        finally {

        }

    }

    public ObjectQueue<OutgoingSMS> getQueue()
    {
        return this.queue;
    }

    static class GsonConverter implements FileObjectQueue.Converter<OutgoingSMS> {
        private final Gson gson;

        public GsonConverter(Gson gson) {
            this.gson = gson;
        }

        @Override public OutgoingSMS from(byte[] bytes) throws IOException {
            Reader reader = new InputStreamReader(new ByteArrayInputStream(bytes));
            return gson.fromJson(reader, OutgoingSMS.class);
        }

        @Override public void toStream(OutgoingSMS s, OutputStream bytes) throws IOException {
            Writer writer = new OutputStreamWriter(bytes);
            gson.toJson(s, writer);
            writer.close();
        }
    }
}


