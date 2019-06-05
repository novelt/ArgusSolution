package org.argus.sms.app.smsConfig.security;

import android.content.Context;

import org.argus.sms.app.R;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by eotin on 17/01/2018.
 */

public class BlackListSettings
{
    public static List<String> getBlackListSettings(Context context)
    {
        List<String> blackList = new ArrayList<>();

        // Enable / Disable possibility to update settings via SMS
        blackList.add(context.getString(R.string.prefs_config_sms_enable).toLowerCase());

        return blackList;
    }
}
