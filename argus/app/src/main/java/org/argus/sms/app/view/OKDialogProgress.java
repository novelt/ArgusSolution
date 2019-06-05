package org.argus.sms.app.view;

import android.app.Dialog;
import android.app.ProgressDialog;
import android.os.Bundle;
import android.support.v4.app.DialogFragment;

/**
 * ProgressDialog fragment
 */
public class OKDialogProgress extends DialogFragment {

    private final static String KEY_MODAL = "key_modal";
    private final static String KEY_MESSAGE = "key_message";

    public static OKDialogProgress newInstance(String message, boolean modal) {
        OKDialogProgress fragment = new OKDialogProgress();
        Bundle b = new Bundle();
        b.putString(KEY_MESSAGE, message);
        b.putBoolean(KEY_MODAL, !modal);
        fragment.setArguments(b);
        return fragment;
    }

    @Override
    public Dialog onCreateDialog(Bundle savedInstanceState) {
        String message = getArguments().getString(KEY_MESSAGE);
        boolean modal= getArguments().getBoolean(KEY_MODAL, true);
        ProgressDialog d = ProgressDialog.show(getActivity(),null,message,true,modal);
        d.setCanceledOnTouchOutside(modal);
        return d;
    }
}