<?php

	// Array for global configuration
	$config=array();

    $config["application_name"]    = "ARGUS";   // ARGUS

	// Application version
	$config["ses_version"]         = "ARGUS v1.0.0 - 22/03/2019";
	$config["schema_version"]      = "1.0.0";

	// Application language (keep it empty for default messages)
	$config["language"]            = "en";

	// MySQL connection credentials
	$config["mysql_server"]        = "127.0.0.1";   // Localhost in IPv4
	$config["mysql_port"]          = "3306";        // port number
    $config["mysql_db"]            = "argus"; 		// argus
	$config["mysql_user"]          = "root";
	$config["mysql_password"]      = "";

	// Applications paths
	$config["path_base"]           		= getcwd().DIRECTORY_SEPARATOR;
	$config["path_data_input"]     		= $config["path_base"]."data".DIRECTORY_SEPARATOR."input".DIRECTORY_SEPARATOR;
	$config["path_data_ouput"]     		= $config["path_base"]."data".DIRECTORY_SEPARATOR."output".DIRECTORY_SEPARATOR;
	$config["path_data_processed"] 		= $config["path_base"]."data".DIRECTORY_SEPARATOR."processed".DIRECTORY_SEPARATOR;
    $config["path_data_work"] 		    = $config["path_base"]."data".DIRECTORY_SEPARATOR."work".DIRECTORY_SEPARATOR;
	$config["path_logs"]           		= $config["path_base"]."logs".DIRECTORY_SEPARATOR;
	$config["path_locale"]         		= $config["path_base"]."locale".DIRECTORY_SEPARATOR;
	$config["path_ressources"]     		= $config["path_base"]."ressources".DIRECTORY_SEPARATOR;
    $config["path_ressources_database"] = $config["path_base"]."ressources".DIRECTORY_SEPARATOR."Database".DIRECTORY_SEPARATOR;
	$config["path_backup"]         		= "C:\\Backup\\SES\\";
    $config["path_ressources_database_initialization_folders"] = ["01_Install", "02_MySQL_timezones", "03_Functions", "04_Views", "05_Stored procedures", "06_Partitions"];


	//Xampp Installation paths
	$config["xampp_path"]           	= "C:\\xampp" . DIRECTORY_SEPARATOR;
	// Php ini
	$config["php_conf_path"]           	= $config["xampp_path"]."php\\php.ini";
	// MySql ini
	$config["mysql_conf_path"]          = $config["xampp_path"]."mysql\\bin\\my.ini";
	// MySql exe
	$config["mysql_exe_path"]           = $config["xampp_path"]."mysql\\bin\\mysql.exe";
	// MySql dump exe
	$config["mysql_dump_exe_path"]      = $config["xampp_path"]."mysql\\bin\\mysqldump.exe";
	// Apache conf
	$config["apache_conf_path"]         = $config["xampp_path"]."apache\\conf\\httpd.conf";
	// Xampp version
	$config["xampp_version_path"]       = $config["xampp_path"]."htdocs\\xampp\\.version";

	// XSD schemas
	$config["path_import_schema"]  		= $config["path_base"]."schemas".DIRECTORY_SEPARATOR."SES-Import.xsd";
	$config["path_export_schema"]  		= $config["path_base"]."schemas".DIRECTORY_SEPARATOR."SES-Export.xsd";

	// HTML Notification duration in milliseconds
	$config["html_notif_duration"] = 1500;

	// Special disease reference for alert
	$config["alert_reference"]     = 'ALERT';

	// Backup retention
	$config["backup_minimum_number"] = 10;
	$config["backup_minimum_day"]    = 15;

	// First day of the epi week
	// Not yet implemented! Only for the Android app
	// Where 1=Monday and 7=Sunday (defaut is 1)
	$config["epi_first_day"] = 1;

	// Android app settings
	$config["android_keyword_id"]        = "ANDROIDID";
	$config["report_keyword_id"]         = "RID";
	$config["android_keyword_ok"]        = "ANDROID_OK";
	$config["android_keyword_threshold"] = "ANDROID_THRESHOLD";

	// ArgusSMS settings
	$config["ArgusSMS_secret"]				= "argus" ;
	$config["ArgusSMS_pendingSms"]			= "4" ;    // 10
	$config["ArgusSMS_errorLimit"]			= "30" ;

    // Gateway diagnosis
    $config["Gateway_alive_minutes"]		= "15" ; // Minutes before considering a gateway is off
    $config["Gateway_max_pending_sms"]		= "50" ; // Number of max pending SMS in queue before considering a gateway is off

    // DISCOVER_SES_SERVER_REQUEST
	$config["ArgusGateway_address"]			= "/ses/argusGateway.php" ;

	// Config SMS format
	$config["config_SMS"]                   = ["AVDCFG#", "AVDACK#", "ARGCFG#", "ARGACK#", "ARLCFG#", "ARLACK#"];
?>