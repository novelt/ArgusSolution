package org.argus.sms.app.activity;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.support.v4.app.Fragment;
import android.view.Gravity;
import android.view.View;
import android.widget.Toast;

import com.roomorama.caldroid.CaldroidFragment;
import com.roomorama.caldroid.CaldroidListener;

import java.util.Calendar;
import java.util.Date;

import fr.openium.androkit.utils.ToastUtils;
import org.argus.sms.app.R;
import org.argus.sms.app.fragment.AbstractFragmentReport;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.utils.HelperCalendar;


/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Abstract Activity to factorize all code in reports (monthly and weekly)
 */
public abstract class AbstractActivityReport extends AbstractActivityCancelOnBackUp implements AbstractFragmentReport.IFragmentReportListener {
    private final static String TAG = AbstractActivityReport.class.getSimpleName();
    private final static boolean DEBUG = true;

    protected AbstractFragmentReport mFragment;
    private Toast mToast;

    /**
     * Called when the report need to be sent
     */
    public abstract void onReportSent();

    @Override
    protected void saveFragmentFromRestoredState(final Fragment f) {
        mFragment = (AbstractFragmentReport) f;
    }

    /**
     * Start the detail method for the specified type and disease
     * @param type Type of sms
     * @param disease name of the desease
     */
    @Override
    public void onStartDetail(TypeSms type, String disease, String label) {
        Intent i = ActivityReportDetail.getStartingIntent(this, type, disease, label);
        startActivity(i);
    }

    /**
     * Show the calendar and handle the selection of a date
     */
    @Override
    public void onShowCalendar() {
        final CaldroidFragment dialogCaldroidFragment = initCalDroid();
        dialogCaldroidFragment.setCaldroidListener(new CaldroidListener() {
            @Override
            public void onSelectDate(Date date, View view) {
                setCalendarNumber(date);
                dialogCaldroidFragment.dismiss();
            }
        });
        dialogCaldroidFragment.show(getSupportFragmentManager(), "TAG");
    }

    /**
     * Initialisation of the calendar with the current date
     * @return the configurated CaldroidFragment
     */
    private CaldroidFragment initCalDroid() {
        Calendar current = HelperCalendar.getCalendarWithCorrectStartWeek(getApplicationContext());
        final CaldroidFragment dialogCaldroidFragment = HelperCalendar.initCalDroid(getApplicationContext());
        dialogCaldroidFragment.setMaxDate(getMaxDate());
        current.add(Calendar.MONTH, -12);
        dialogCaldroidFragment.setMinDate(current.getTime());
        return dialogCaldroidFragment;
    }

    protected abstract Date getMaxDate();

    protected void setCalendarNumber(final Date selectedDate) {
        mFragment.setCalendarNumber(selectedDate);
    }

    /**
     * Display of the report not sent message
     */
    @Override
    protected void showDraftToast() {
        mToast = ToastUtils.displayToastAndCancelOldIfExists(mToast,this,R.string.report_not_sent,Toast.LENGTH_LONG, Gravity.CENTER);
    }

    @Override
    public void onBackPressed() {
        quitReport();
    }

    private void quitReport() {
        final Activity self = this;

        new AlertDialog.Builder(this)
            .setIcon(android.R.drawable.ic_dialog_alert)
            .setTitle(getString(R.string.warning))
            .setMessage(getString(R.string.sure_quit_report))
            .setPositiveButton(getString(R.string.yes), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    mFragment.loadConfigClean();
                    showDraftToast();
                    self.finish();
                }

            })
            .setNegativeButton(getString(R.string.no), null)
            .show();
    }
}
