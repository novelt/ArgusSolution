package org.argus.sms.app.view;

import android.app.DatePickerDialog;
import android.content.Context;

/**
 * Specific DatePickerDialog with a static title
 *
 * Created by Olivier Goutet.
 * Openium 2015
 */
public class DatePickerDialogStaticTitle extends DatePickerDialog {
    private String mStaticTitle;

    public DatePickerDialogStaticTitle(final Context context, final OnDateSetListener callBack, final int year, final int monthOfYear, final int dayOfMonth) {
        super(context, callBack, year, monthOfYear, dayOfMonth);
    }

    public DatePickerDialogStaticTitle(final Context context, final int theme, final OnDateSetListener listener, final int year, final int monthOfYear, final int dayOfMonth) {
        super(context, theme, listener, year, monthOfYear, dayOfMonth);
    }

    /**
     * Set the static title
     * @param title the title to display
     */
    public void setStaticTitle(String title){
        mStaticTitle = title;
    }

    @Override
    public void setTitle(final CharSequence title) {
        super.setTitle(mStaticTitle);
    }
}
