<?php

	// Default configuration settings
	$config_defaults=array(

		// Default reminder values
        // TODO : Check if used
		"weekly_reminder_overrun_minutes" => array(
			"Description"=>_("Number of minutes before sending a reminder for the weekly report"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>960, 
			"ListInteger"=>array(), 
			"HasString"=>FALSE, 
			"DefaultString"=>"",
			"DisplaySettings"=>false,
		),
        // TODO : Check if used
		"monthly_reminder_overrun_minutes" => array(
			"Description"=>_("Number of minutes before sending a reminder for the monthly report"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>3840, 
			"ListInteger"=>array(), 
			"HasString"=>FALSE, 
			"DefaultString"=>"",
			"DisplaySettings"=>false,
		),
		

		// Global keywords definitions
        // TODO : Fix this keyword
		"global_keyword_disease" => array(
			"Description"=>_("Global keywords for disease"),
			"HasInteger"=>FALSE, 
			"DefaultInteger"=>0, 
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>"DISEASE", // DIS
			"DisplaySettings"=>false,
		),
        // TODO : Fix this keyword
		"global_keyword_week" => array(
			"Description"=>_("Global keywords for week"),
			"HasInteger"=>FALSE, 
			"DefaultInteger"=>0, 
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>"WEEK",  // WK
			"DisplaySettings"=>false,
		),
        // TODO : Fix this keyword
		"global_keyword_month" => array(
			"Description"=>_("Global keywords for month"),
			"HasInteger"=>FALSE, 
			"DefaultInteger"=>0, 
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>"MONTH", // MH
			"DisplaySettings"=>false,
		),
        // TODO : Fix this keyword
		"global_keyword_year" => array(
			"Description"=>_("Global keywords for year"),
			"HasInteger"=>FALSE, 
			"DefaultInteger"=>0, 
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>"YEAR", // YR
			"DisplaySettings"=>false,
		),
        // TODO : Fix this keyword
		"global_keyword_report" => array(
			"Description"=>_("Global keywords for report"),
			"HasInteger"=>FALSE, 
			"DefaultInteger"=>0, 
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>"REPORT",  // RT
			"DisplaySettings"=>false,
		),
        // TODO : Fix this keyword
		"global_keyword_alert" => array(
			"Description"=>_("Global keywords for alert"),
			"HasInteger"=>FALSE, 
			"DefaultInteger"=>0, 
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>"ALERT",   // AT
			"DisplaySettings"=>false,
		),
		"global_keyword_android" => array(
			"Description"=>_("Global keywords for Android automatic setup"),
			"HasInteger"=>FALSE, 
			"DefaultInteger"=>0, 
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>"ANDROID",
			"DisplaySettings"=>false,
		),
		// Message handling
		"enable_test_mode" => array(
			"Description"=>_("Enable Test Mode : Incoming test data are not saved"),
			"HasInteger"=>TRUE,
			"DefaultInteger"=>1,
			"ListInteger"=>array("No"=>0, "Yes"=>1),
			"HasString"=>FALSE,
			"DefaultString"=>"",
			"DisplaySettings"=>true,
		),

		"enable_forward_data" => array(
			"Description"=>_("Enable Forward Data : Alerts are forwarded to the recipient list"),
			"HasInteger"=>TRUE,
			"DefaultInteger"=>1,
			"ListInteger"=>array("No"=>0, "Yes"=>1),
			"HasString"=>FALSE,
			"DefaultString"=>"",
			"DisplaySettings"=>true,
		),

		"reponse_unknown_contact" => array(
			"Description"=>_("Respond to an unknown contact"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>1, 
			"ListInteger"=>array("No"=>0, "Yes"=>1), 
			"HasString"=>FALSE, 
			"DefaultString"=>"",
			"DisplaySettings"=>true,
		),
		"reponse_parsing_error" => array(
			"Description"=>_("Respond if the message is malformed"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>1, 
			"ListInteger"=>array("No"=>0, "Yes"=>1), 
			"HasString"=>FALSE, 
			"DefaultString"=>"",
			"DisplaySettings"=>true,
		),
		"enable_alert" => array(
			"Description"=>_("Enable receiving alerts"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>1, 
			"ListInteger"=>array("No"=>0, "Yes"=>1), 
			"HasString"=>FALSE, 
			"DefaultString"=>"",
			"DisplaySettings"=>true,
		),
		"enable_report" => array(
			"Description"=>_("Enable receiving reports"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>1, 
			"ListInteger"=>array("No"=>0, "Yes"=>1), 
			"HasString"=>FALSE, 
			"DefaultString"=>"",
			"DisplaySettings"=>true,
		),
		"enable_report_ack" => array(
			"Description"=>_("Enable sending acknowledgement when a report or alert is received"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>1, 
			"ListInteger"=>array("No"=>0, "Yes"=>1), 
			"HasString"=>FALSE, 
			"DefaultString"=>"",
			"DisplaySettings"=>true,
		),

		// TODO : To delete
		"server_phone_number" => array(
			"Description"=>_("CSV list of phone numbers connected to the server (international format)"),
			"HasInteger"=>FALSE, 
			"DefaultInteger"=>0, 
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>"+XXXXX",
			"DisplaySettings"=>true,
		),

		// Limit size of SMS message sent
		"max_length_sms_sent" => array(
			"Description"=>_("Maximum length of the SMS messages sent"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>155,
			"ListInteger"=>array(), 
			"HasString"=>FALSE, 
			"DefaultString"=>"",
			"DisplaySettings"=>true,
		),		

		// Limit levels in sites import
		"import_sites_maximum_levels" => array(
			"Description"=>_("Maximum number of site levels to check when importing the XML configuration"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>20,
			"ListInteger"=>array(), 
			"HasString"=>FALSE, 
			"DefaultString"=>"",
			"DisplaySettings"=>false,
		),

		// Specific setup variables for the Android app
		"android_app_confirmation_alert" => array(
			"Description"=>_("Android App: Delay in minutes and message if the alert confirmation is not received"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>60,
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>_("The alert was not received. Contact your supervisor"), // L alerte n a pas ete recue. Contactez votre superviseur
			"DisplaySettings"=>true,
		),
		"android_app_confirmation_week" => array(
			"Description"=>_("Android App: Delay in minutes and message if the week report confirmation is not received"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>120, 
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>_("The weekly report was not received. Send it back from history"), // Le rapport hebdomadaire n a pas ete recu. Renvoyez le depuis l historique
			"DisplaySettings"=>true,
		),
		"android_app_confirmation_month" => array(
			"Description"=>_("Android App: Delay in minutes and message if the month report confirmation is not received"),
			"HasInteger"=>TRUE, 
			"DefaultInteger"=>480,
			"ListInteger"=>array(), 
			"HasString"=>TRUE, 
			"DefaultString"=>_("The monthly report was not received. Send it back from history"), // Le rapport mensuel n a pas ete recu. Renvoyez le depuis l historique
			"DisplaySettings"=>true,
		),

		// Messages sent by the server to the Android user
		"server_alert_not_forwarded_ack" => array(
			"Description"=>_("Server: Acknowledge message when alert is received but not forwarded to others contacts"),
			"HasInteger"=>FALSE,
			"DefaultInteger"=>0,
			"ListInteger"=>array(),
			"HasString"=>TRUE,
			"DefaultString"=>_("The alert was not forwarded to your supervisor, contact her or him"), // L alerte n a pas ete transmise à votre superviseur, contactez le
			"DisplaySettings"=>true,
		),
		"server_alert_forwarded_ack" => array(
			"Description"=>_("Server: Acknowledge message when alert is received and forwarded to others contacts \n ** (%1\$d) = Number of contact reached"),
			"HasInteger"=>FALSE,
			"DefaultInteger"=>0,
			"ListInteger"=>array(),
			"HasString"=>TRUE,
			"DefaultString"=>_("Alert was received and forwarded to %1\$d contacts"), // No message
			"DisplaySettings"=>true,
		),
		"server_report_received_ack" => array(
			"Description"=>_("Server: Acknowledge message when disease is received \n ** (%1\$s) = Period \n ** (%2\$s) = Disease \n ** (%3\$s) = Start Date"),
			"HasInteger"=>FALSE,
			"DefaultInteger"=>0,
			"ListInteger"=>array(),
			"HasString"=>TRUE,
			"DefaultString"=>_("%1\$s Report for disease %2\$s starting on %3\$s was received"), // No message
			"DisplaySettings"=>true,
		),
		"server_alert_forward" => array(
			"Description"=>_("Server: Message when alert is forwarded \n ** (%1\$s) = Site \n ** (%2\$s) = Contact Phone Number"),
			"HasInteger"=>FALSE,
			"DefaultInteger"=>0,
			"ListInteger"=>array(),
			"HasString"=>TRUE,
			"DefaultString"=>_("Call site %1\$s (%2\$s):"), // Appelez le site %1$s (%2$s):
			"DisplaySettings"=>true,
		),
		"server_alert_threshold" => array(
			"Description"=>_("Server: Message when threshold is reached \n ** (%1\$d) = Threshold max value \n ** (%2\$s) = Disease \n ** (%3\$d) = Threshold value reached \n ** (%4\$s) = Period \n ** (%5\$s) = Start Date"),
			"HasInteger"=>FALSE,
			"DefaultInteger"=>0,
			"ListInteger"=>array(),
			"HasString"=>TRUE,
			"DefaultString"=>_("Warning! The alert threshold has been reached with %3\$d cases of \"%2\$s\" the week of %5\$s"), // Attention! Le seuil d alerte a ete atteint avec %3$d cas de "%2$s" la semaine du %5$s
			"DisplaySettings"=>true,
		),
	);

?>