package org.argus.sms.app.config;

import org.argus.sms.app.model.Config;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ConfigTestData {


    public static Config getConfig() {
        Config config = Config.getInstance(null);
        config.addKeyAndValue(Config.KEYWORD_WEEK, "WEEK");
        config.addKeyAndValue(Config.KEYWORD_MONTH, "MONTH");
        config.addKeyAndValue(Config.KEYWORD_YEAR, "YEAR");
        config.addKeyAndValue(Config.KEYWORD_WEEKLY, "REPORT");
        config.addKeyAndValue(Config.KEYWORD_MONTHLY, "REPORTMONTHY");
        config.addKeyAndValue(Config.KEYWORD_ALERT, "ALERT");
        config.addKeyAndValue(Config.KEYWORD_DISEASE, "DISEASE");
        config.addKeyAndValue(Config.KEYWORD_ID,Config.KEYWORD_ID);
        return config;
    }
}
