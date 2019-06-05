package org.argus.gateway;

import android.app.Dialog;
import android.content.Context;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;

import java.util.Random;

/**
 * Created by alexandre on 20/01/16.
 * Class used to test sms received and forwarder
 */
public class TestMessageReceive {

    private static final String ANDROID_ID_SYNCH = "Android ANDROIDID=";
    private static final String ANDROID_ID = ", ANDROIDID=";
    private static final String SMS_SYNCH = ", VERSION=1.0";
    private static final String[] SMS_REPORT = {"REPORT DISEASE=CHOLERA , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=COQUELUCHE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=DECESMATERNELS , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=DIPTHERIE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=DRACUNCULOSE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=DYSENTERIEBACILLAIRE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=FIEVREHEMORRAGIQUE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=FIEVREJAUNE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=FIEVRETYPHOIDE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=GRIPPE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=MALNUTRITIONAIGUEMODEREE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=MALNUTRITIONAIGUESEVERE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=MALNUTRITIONCHRONIQUE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=MAPI , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=MENINGITE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=PFA , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=RAGE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=ROUGEOLE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=SYNDROMERESPIRATOIREAIGUSEVERE , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0" ,
            "REPORT DISEASE=TMN , YEAR=2016 , WEEK=2 , CASES=0 , DECES=0"};
    private static final String SMS_ALERT = "ALERT EVENEMENT=mtrdt , DATE=08/01/2016 , LIEU=pol , CAS=0 , HOSPITALISATION=0 , DECES=0";


    private App app;



    public TestMessageReceive(App app) {
        this.app = app;
    }

    /**
     * Draw a dialog box asking for sms number and call send for each sms.
     * @param context
     */
    public void sendSms(final Context context) {
        final Dialog d = new Dialog(context);
        d.setTitle("Choose the number of sms to send.");
        d.setContentView(R.layout.test_sms_dialog);
        Button b1 = (Button) d.findViewById(R.id.btn_valid);
        Button b2 = (Button) d.findViewById(R.id.btn_cancel);
        final EditText np = (EditText) d.findViewById(R.id.txt_number);
        b1.setOnClickListener(new View.OnClickListener()
        {
            @Override
            public void onClick(View v) {
                int number = 0;
                try {
                    number = Integer.parseInt(np.getText().toString());
                    for (int i = 0; i < number; i++) {
                        send(context);
                    }
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

    /**
     * Add a fake sms to the ArgusGateway inbox.
     * each sms are generated with random Android id between 1 and 1000
     * the timestamp is also random
     * @param ctx
     */
    private void send(Context ctx) {
        try {
            int smsNum = getRandomNumberBetween(0, 3);
            String body = "";
            switch (smsNum) {
                case 0:
                    body = ANDROID_ID_SYNCH + getRandomNumberBetween(1, 1000) + " " + SMS_SYNCH;
                    break;
                case 1:
                    body = SMS_REPORT[getRandomNumberBetween(1, SMS_REPORT.length)];
                    break;
                default:
                    body = SMS_ALERT + ANDROID_ID + getRandomNumberBetween(1, 1000);
                    break;
            }
            long timestamp = 12569537329L;
            IncomingMessage sms = new IncomingSms(app, "+41754139319", timestamp + getRandomNumberBetween(0, 1000000), body);

            if (sms.isForwardable()) {
                app.inbox.forwardMessage(sms);
            } else {
                app.log("Ignoring incoming SMS from " + sms.getFrom());
            }
        } catch (Throwable ex) {
            app.logError("Unexpected error in SmsReceiver", ex, true);
        }
    }

    /**
     * Get a random number between min and max
     * @param min value of the generated number
     * @param max value of the generated number
     * @return the random number generated
     */
    public static int getRandomNumberBetween(int min, int max) {
        Random foo = new Random();
        int randomNumber = foo.nextInt(max - min) + min;
        if(randomNumber == min) {
            // Since the random number is between the min and max values, simply add 1
            return min;
        }
        else {
            return randomNumber;
        }

    }
}
