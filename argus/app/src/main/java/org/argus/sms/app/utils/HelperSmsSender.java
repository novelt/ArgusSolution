package org.argus.sms.app.utils;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.IntentFilter;
import android.telephony.SmsManager;
import android.telephony.TelephonyManager;
import android.text.TextUtils;
import android.util.Log;
import android.widget.Toast;

import java.util.Date;

import fr.openium.androkit.utils.ToastUtils;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.TypeSms;

/**
 * SMS sender utility class
 * <p/>
 * Created by Olivier Goutet.
 * Openium 2014
 */
public final class HelperSmsSender {
    private final static String TAG = HelperSmsSender.class.getSimpleName();
    private final static boolean DEBUG = true;

    private static Toast mToast;


    /**
     * Interface used to have the sms send result
     */
    public interface SmsListener<T> {

        /**
         * SMS sent successfully
         * @param tag tag sent by the user when the sms send command has been launched
         */
        void onSmsSentSuccess(final String tag, final T sms);

        /**
         * Sms sent error
         * @param tag tag sent by the user when the sms send command has been launched
         * @param error SmsManager.RESULT_ERROR_* codes
         */
        void onSmsSentError(final String tag, final T sms, final int error);

    }

    /**
     * Send an sms for a specific type
     *
     * @param context application context
     * @param type    type of sms
     * @param sms     sms object
     * @return true if the sms is successfully sent, false otherwise
     */
    public static void sendSms(Context context, TypeSms type, final Sms sms, final SmsListener listener, final String tag) {
        String text = sms.toSms(type, Config.getInstance(context), true);
        sendSms(context, text, listener, tag, sms);
    }

    /**
     * Send text from sms
     *
     * @param context application context
     * @param text    text to send
     * @return true if the sms is successfully sent, false otherwise
     */
    public static void sendSms(Context context, String text, final SmsListener listener, final String tag, final Sms sms) {
        String serverPhoneNumber = HelperPreference.getServerPhoneNumber(context);
        sendSms(context, serverPhoneNumber, text, listener, tag, sms);
    }

    /**
     * Send text to a specific number by sms
     *
     * @param context      application context
     * @param serverNumber server phone number
     * @param text         text to send
     * @param listener     listener to receive the sms send result
     * @param tag          tag to receive in the listener when the sms is sent
     * @param sms          sms to receive in the listener when the sms is sent
     * @return true if the sms is successfully sent, false otherwise
     */
    public static <T> void sendSms(Context context, String serverNumber, String text, final SmsListener listener, final String tag, final T sms) {
        boolean result;
        if (TextUtils.isEmpty(serverNumber)) {
            mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, context, org.argus.sms.app.R.string.aucun_serveur_configure, Toast.LENGTH_LONG);
            result = false;
        } else {
            SmsManager manager = SmsManager.getDefault();

            String SENT = "ses.sent";
            Intent sentIntent = new Intent(SENT);
            PendingIntent sentPI = PendingIntent.getBroadcast(
                    context.getApplicationContext(), 0, sentIntent,
                    PendingIntent.FLAG_UPDATE_CURRENT);

     /* Register for SMS send action */

            context.registerReceiver(new BroadcastReceiver() {

                @Override
                public void onReceive(Context context, Intent intent) {
                    String result = null;
                    int resultCode = getResultCode();
                    switch (resultCode) {
                        case Activity.RESULT_OK:
                            break;
                        case SmsManager.RESULT_ERROR_RADIO_OFF:
                            result = "Radio off";
                            break;
                        case SmsManager.RESULT_ERROR_NULL_PDU:
                            result = "No PDU defined";
                            break;
                        case SmsManager.RESULT_ERROR_NO_SERVICE:
                            result = "No service";
                            break;
                        case SmsManager.RESULT_ERROR_GENERIC_FAILURE:
                            // http://stackoverflow.com/questions/12987909/whats-meant-by-result-errors-of-smsmanager
                            result = "Transmission failed";
                            // getAlertDialog(context.getString(R.string.error_title), context.getString(R.string.check_credit), context);
                            break;
                    }
                    if (BuildConfig.DEBUG && DEBUG) {
                        Log.d(TAG, "onReceive result=" + result);
                    }
                    if (listener != null) {
                        if (resultCode == Activity.RESULT_OK) {
                            listener.onSmsSentSuccess(tag, sms);
                        } else {
                            listener.onSmsSentError(tag, sms, resultCode);
                        }
                    }

                    try {
                        context.unregisterReceiver(this);
                    }
                    catch (IllegalArgumentException ex)
                    {
                        Log.w(TAG, "IllegalArgumentException catched on specific Android v2.3.6");
                        ex.printStackTrace();
                    }

                }

            }, new IntentFilter(SENT));
            manager.sendTextMessage(serverNumber, null, text, sentPI, null);
            if (BuildConfig.DEBUG && DEBUG) {
                Log.d(TAG, "sendSms server= " + serverNumber + " sms= " + text);
            }
        }
    }

    /**
     * Check if the network is available to send SMS
     *
     * @param context application context
     * @return true if network is available to send SMS, false otherwise
     */
    @SuppressLint("NewApi")
    public static boolean isSmsNetworkAvailable(Context context) {
        TelephonyManager tlm = (TelephonyManager) context.getSystemService(Context.TELEPHONY_SERVICE);

        // http://stackoverflow.com/questions/15924099/check-if-a-phone-can-send-sms
        //http://stackoverflow.com/questions/9598575/mobile-network-presence-detection
        int simCardState = tlm.getSimState();
       return (simCardState == TelephonyManager.SIM_STATE_READY);

    }

    public static void getAlertDialog(String title, String content, Context ctx) {
        AlertDialog alertDialog = new AlertDialog.Builder(ctx).create();
        alertDialog.setTitle(title);
        alertDialog.setMessage(content);
        alertDialog.setButton(AlertDialog.BUTTON_NEUTRAL, ctx.getString(R.string.ok),
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.dismiss();
                    }
                });
        alertDialog.show();
    }

    public static void displayStandardErrorText(Context context){
        mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, context, context.getString(org.argus.sms.app.R.string.no_network), Toast.LENGTH_LONG);
    }

    public static void displayGsmNetworkErrorText(Context context){
        mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast, context, context.getString(org.argus.sms.app.R.string.check_credit), Toast.LENGTH_LONG);
    }

   /* public static void sendConfigSms(Context context, String to, String message, final SmsListener listener){

    }*/
}
