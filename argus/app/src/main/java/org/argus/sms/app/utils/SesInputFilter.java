package org.argus.sms.app.utils;

import android.text.InputFilter;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.TextUtils;
import android.util.Log;

import fr.openium.androkit.ConfigApp;

/**
 * Created by Olivier Goutet.
 * Openium 2015
 */
public class SesInputFilter implements InputFilter {
private final static String TAG = SesInputFilter.class.getSimpleName();
private final static boolean DEBUG = true;

    @Override
    public CharSequence filter(CharSequence source, int start, int end, Spanned dest, int dstart, int dend) {
        boolean keepOriginal = true;
        StringBuilder sb = new StringBuilder(end - start);
        for (int i = start; i < end; i++) {
            char c = source.charAt(i);
            if (isCharAllowed(c)) // put your condition here
                sb.append(c);
            else
                keepOriginal = false;
        }
        if (keepOriginal)
            return null;
        else {
            if (ConfigApp.DEBUG && DEBUG){
                Log.d(TAG, String.format("filter source.length()=%d, start=%d, end=%d",source.length(),start,end));
            }
            if (source instanceof Spanned) {
                SpannableString sp = new SpannableString(sb);
                end = sp.length();
                if (start <= sp.length() || end <= sp.length()) {
                    TextUtils.copySpansFrom((Spanned) source, start, end, null, sp, 0);
                    return sp;
                }else{
                    return sb;
                }

            } else {
                return sb;
            }
        }
    }

    private boolean isCharAllowed(char c) {
        return Character.isLetterOrDigit(c) || Character.isSpaceChar(c) || '\'' == c;
    }
}
