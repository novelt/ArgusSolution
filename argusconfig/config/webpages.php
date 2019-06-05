<?php

	// List of web pages for administration site
	$web_pages=array(

		_("Display")=>array(
			array("Title"=>_("Welcome"), "URL"=>"page_welcome.php"),
			array("Title"=>_("Sites"), "URL"=>"page_list_sites.php"),
			array("Title"=>_("Contacts"), "URL"=>"page_list_contacts.php"),
			array("Title"=>_("Diseases"), "URL"=>"page_list_diseases.php"),
			array("Title"=>_("Thresholds"), "URL"=>"page_list_thresholds.php"),
			array("Title"=>_("Reports"), "URL"=>"page_list_reports.php"),
			array("Title"=>_("License"), "URL"=>"page_license.php"),
		),
	
		_("Maintenance")=>array(
			array("Title"=>_("Run tasks"), "URL"=>"page_run_tasks.php"),
			array("Title"=>_("Detailed diagnosis"), "URL"=>"page_diagnosis.php"),
			array("Title"=>_("Change settings"), "URL"=>"page_settings.php"),
			array("Title"=>_("Setup and backup"), "URL"=>"page_setup.php"),
		),

	);

?>