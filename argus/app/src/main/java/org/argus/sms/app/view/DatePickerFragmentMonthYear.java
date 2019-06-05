package org.argus.sms.app.view;

import android.app.DatePickerDialog;
import android.app.Dialog;
import android.content.res.Resources;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.support.v4.app.DialogFragment;
import android.util.Log;
import android.view.View;
import android.widget.DatePicker;

import java.util.Calendar;
import java.util.GregorianCalendar;

/**
 * Specific date picker fragment with month and year
 */
public class DatePickerFragmentMonthYear extends DialogFragment implements DatePickerDialog.OnDateSetListener {

    private Handler mHandler;

    public interface Listener {
        public void onDateSelected(int month,int year);
    }

    private Listener mListener;
    private final static String KEY_MONTH = "KEY_MONTH";
    private final static String KEY_YEAR = "KEY_YEAR";

    public static DatePickerFragmentMonthYear newInstance(int month, int year){
        DatePickerFragmentMonthYear fragment = new DatePickerFragmentMonthYear();
        Bundle b = new Bundle();
        b.putInt(KEY_MONTH, month);
        b.putInt(KEY_YEAR, year);
        fragment.setArguments(b);
        return fragment;
    }



    public void setListener(final Listener listener) {
        mListener = listener;
    }

    @Override
    public Dialog onCreateDialog(Bundle savedInstanceState) {

        mHandler = new Handler();
        // Use the current date as the default date in the picker
        int year = getArguments().getInt(KEY_YEAR);
        int month = getArguments().getInt(KEY_MONTH);
        int day = 1;

        // Create a new instance of DatePickerDialog and return it
        final DatePickerDialogStaticTitle dpd = new DatePickerDialogStaticTitle(getActivity(), this, year, month, day);
        dpd.setStaticTitle(getString(org.argus.sms.app.R.string.month));
        if (android.os.Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
            Calendar c = new GregorianCalendar();
            c.add(Calendar.MONTH, -1);
            dpd.getDatePicker().setMaxDate(c.getTimeInMillis());
        }
        removeDay(dpd);
        mHandler.post(new Runnable() {
            @Override
            public void run() {
                removeDayCompat(dpd);
            }
        });
        return dpd;
    }

    /**
     * Called when the user selected a date in the picker
     * @param view view displayed
     * @param year year selected
     * @param month month selected
     * @param day day selected
     */
    public void onDateSet(DatePicker view, int year, int month, int day) {
        if (mListener != null){
            mListener.onDateSelected(month,year);
        }
    }

    /**
     * Remove the day part of the picker
     * @param dpd DatePickerDialog to remove the field in
     */
    private void removeDay(DatePickerDialog dpd){
        try{
            java.lang.reflect.Field[] datePickerDialogFields = dpd.getClass().getSuperclass().getDeclaredFields();
            for (java.lang.reflect.Field datePickerDialogField : datePickerDialogFields) {
                if (datePickerDialogField.getName().equals("mDatePicker")) {
                    datePickerDialogField.setAccessible(true);
                    DatePicker datePicker = (DatePicker) datePickerDialogField.get(dpd);
                    java.lang.reflect.Field[] datePickerFields = datePickerDialogField.getType().getDeclaredFields();
                    for (java.lang.reflect.Field datePickerField : datePickerFields) {
                        Log.i("test", datePickerField.getName());
                        if ("mDaySpinner".equals(datePickerField.getName())) {
                            datePickerField.setAccessible(true);
                            Object dayPicker = new Object();
                            dayPicker = datePickerField.get(datePicker);
                            ((View) dayPicker).setVisibility(View.GONE);
                        }
                    }
                }

            }
        }catch(Exception ex){
        }
    }

    private void removeDayCompat(final DatePickerDialogStaticTitle dpd) {
        try{
            dpd.findViewById(Resources.getSystem().getIdentifier("day", "id", "android")).setVisibility(View.GONE);
        }catch(Exception e){

        }
    }

}