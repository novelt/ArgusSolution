package org.argus.gateway;

import android.Manifest;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;

import java.util.ArrayList;
import java.util.Locale;

public class TestMessagePackage {

    private App app;

    public TestMessagePackage(App app) {
        this.app = app;
    }

    /**
     * Draw a dialog box asking for sms number and call send for each sms.
     * @param context
     */
    public void sendSms(final Context context) {
        final Dialog d = new Dialog(context);
        d.setTitle("Enter the To number");
        d.setContentView(R.layout.test_sms_dialog);
        Button b1 = (Button) d.findViewById(R.id.btn_valid);
        Button b2 = (Button) d.findViewById(R.id.btn_cancel);
        final EditText np = (EditText) d.findViewById(R.id.txt_number);
        b1.setOnClickListener(new View.OnClickListener()
        {
            @Override
            public void onClick(View v) {
                String phoneNumber = "";
                try {
                    phoneNumber = np.getText().toString();
                    send(phoneNumber);
                }
                catch(Exception e) {
                }
                d.dismiss();
            }
        });
        b2.setOnClickListener(new View.OnClickListener()
        {
            @Override
            public void onClick(View v) {
                d.dismiss();
            }
        });
        d.show();
    }


    private void send(String phoneNumber)
    {

        ArrayList<String> bodyParts = new ArrayList<>();
        bodyParts.add("Test message from slave " + 1);

        String packageName = App.SLAVE_PACKAGE_NAME + String.format(Locale.US, "%02d", 1);
        String intentName = packageName + App.OUTGOING_SMS_INTENT_SUFFIX;
        Intent intent = new Intent(intentName, Uri.withAppendedPath(App.OUTGOING_URI, "testSMS"));
        intent.putExtra(App.OUTGOING_SMS_EXTRA_DELIVERY_REPORT, false);
        intent.putExtra(App.OUTGOING_SMS_EXTRA_TO, phoneNumber);
        intent.putExtra(App.OUTGOING_SMS_EXTRA_BODY, bodyParts);
        intent.addFlags(Intent.FLAG_INCLUDE_STOPPED_PACKAGES);

        app.sendBroadcast(intent, Manifest.permission.SEND_SMS);
    }

}
