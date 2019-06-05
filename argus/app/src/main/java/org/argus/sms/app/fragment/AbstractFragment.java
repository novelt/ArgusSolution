package org.argus.sms.app.fragment;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.support.v4.app.Fragment;
import android.widget.Toast;

import org.argus.sms.app.R;
import org.argus.sms.app.utils.HelperSmsSender;

import butterknife.ButterKnife;

/**
 * Abstract fragment common for all fragments of application
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public abstract class AbstractFragment extends Fragment {

    protected Context mContext;

    @Override
    public void onAttach(final Activity activity) {
        super.onAttach(activity);
        mContext = activity.getApplicationContext();
    }

    @Override
    public void onDestroyView() {
        ButterKnife.reset(this);
        super.onDestroyView();
    }

    /**
     * Display Message to inform user that he cannot send SMS at this times
     */
    protected boolean checkConnectivityToSendSms(boolean alertDialog, DialogInterface.OnClickListener clickListener){
        if (! HelperSmsSender.isSmsNetworkAvailable(mContext)){
            if (alertDialog){

                new AlertDialog.Builder(this.getActivity())
                        .setIcon(android.R.drawable.ic_dialog_alert)
                        .setTitle(getString(R.string.warning))
                        .setMessage(getString(R.string.no_network_alert))
                        .setNeutralButton(getString(R.string.ok), clickListener)
                        .show();

            }else{
                HelperSmsSender.displayStandardErrorText(mContext);
            }

            return true ;
        }

        return false;
    }
}
