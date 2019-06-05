package org.argus.gateway;

import android.app.Application;
import android.content.Intent;
import android.telephony.PhoneNumberUtils;
import android.test.ApplicationTestCase;

import org.argus.gateway.receiver.SmsReceiver;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.lang.reflect.Method;
import java.util.Calendar;
import java.util.GregorianCalendar;
import java.util.Random;

/**
 * Created by alexandre on 27/01/16.
 * Used to test the Broadcast receiver : SmsReceive
 */
public class TestSmsReceiver extends ApplicationTestCase<App> {

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

    private SmsReceiver mReceiver;
    private Application mApplication;
    private App         app;

    public TestSmsReceiver() {
        super(App.class);
    }

    /**
     * function called when test are starting (like onCreate for an activity..)
     * @throws Exception
     */
    @Override
    protected void setUp() throws Exception
    {
        super.setUp();

        mReceiver = new SmsReceiver(true);
        createApplication();
        mApplication = getApplication();
        app = (App) mApplication.getApplicationContext();
        app.saveBooleanSetting("enabled", true);
    }

    /**
     * Test that send multiple call to OnReceive of the Broadcast receiver SmsReciever
     * @throws Exception
     */
    public void testOnReceive() throws Exception {
        for (int i = 0; i < 10; i++) {
            Intent intent = new Intent();
            intent.putExtra("format", "3gpp");
            intent.putExtra("pdus", new Object[]{createFakeSms("+41754139319", generateBody())});
            intent.setAction("android.provider.Telephony.SMS_RECEIVED");
            mReceiver.onReceive(mApplication, intent);
            Thread.sleep(200);
        }
        assertTrue((app.inbox.size() < 11 && app.inbox.size() > 5));
    }

    /**
     * Create a pdu (encoded sms to simulate sms reception) from the message body and sender
     * @param sender source of the message
     * @param body of the message
     * @return the pdu
     */
    private static byte[] createFakeSms(String sender, String body) {
        byte[] pdu = null;
        byte[] scBytes = PhoneNumberUtils
                .networkPortionToCalledPartyBCD("0000000000");
        byte[] senderBytes = PhoneNumberUtils
                .networkPortionToCalledPartyBCD(sender);
        int lsmcs = scBytes.length;
        byte[] dateBytes = new byte[7];
        Calendar calendar = new GregorianCalendar();
        dateBytes[0] = reverseByte((byte) (calendar.get(Calendar.YEAR)));
        dateBytes[1] = reverseByte((byte) (calendar.get(Calendar.MONTH) + 1));
        dateBytes[2] = reverseByte((byte) (calendar.get(Calendar.DAY_OF_MONTH)));
        dateBytes[3] = reverseByte((byte) (calendar.get(Calendar.HOUR_OF_DAY)));
        dateBytes[4] = reverseByte((byte) (calendar.get(Calendar.MINUTE)));
        dateBytes[5] = reverseByte((byte) (calendar.get(Calendar.SECOND)));
        dateBytes[6] = reverseByte((byte) ((calendar.get(Calendar.ZONE_OFFSET) + calendar
                .get(Calendar.DST_OFFSET)) / (60 * 1000 * 15)));
        try {
            ByteArrayOutputStream bo = new ByteArrayOutputStream();
            bo.write(lsmcs);
            bo.write(scBytes);
            bo.write(0x04);
            bo.write((byte) sender.length());
            bo.write(senderBytes);
            bo.write(0x00);
            bo.write(0x00); // encoding: 0 for default 7bit
            bo.write(dateBytes);
            try {
                String sReflectedClassName = "com.android.internal.telephony.GsmAlphabet";
                Class cReflectedNFCExtras = Class.forName(sReflectedClassName);
                Method stringToGsm7BitPacked = cReflectedNFCExtras.getMethod(
                        "stringToGsm7BitPacked", new Class[] { String.class });
                stringToGsm7BitPacked.setAccessible(true);
                byte[] bodybytes = (byte[]) stringToGsm7BitPacked.invoke(null,
                        body);
                bo.write(bodybytes);
            } catch (Exception e) {
            }

            pdu = bo.toByteArray();
        } catch (IOException e) {
        }

        return pdu;
    }

    private static byte reverseByte(byte b) {
        return (byte) ((b & 0xF0) >> 4 | (b & 0x0F) << 4);
    }

    /**
     * Generate random valid message to send
     * @return the message body to send
     */
    private static String generateBody() {
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
        return body;
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
