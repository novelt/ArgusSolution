<?php

	// Global declarations
	require_once ("./tools/web_template.php");
	
	// Increase the default maximum execution time (in seconds)
	set_time_limit(0);
	
	// Start
	$ts_start=time();
	LogMessage("tasks", _("Starting processing tasks"));
	
	// Configuration import
	require_once("./tools/import_configuration.php");
	echo (_("Import configuration files\n==========================\n"));
	echo ("\n");
	ImportConfiguration();
	echo ("\n");
	$Summary=GetDatabaseRecordsCount();
	echo ($Summary);
	LogMessage("tasks", $Summary);	
	echo ("\n");
	
	// Report export
	require_once("./tools/export_reports.php");
	echo ("\n");
	echo (_("Export reports\n==============\n"));
	echo ("\n");
	ExportReports();

	// Diagnosis
	echo ("\n");
	echo (_("Diagnosis\n=========\n"));
	echo ("\n");
	require_once("./tools/diagnosis.php");
	$diagnosis=run_all_diagnosis();
	echo(sprintf(_("Results: %d tests run with %d errors")."\n",$diagnosis['nb_tests'],$diagnosis['nb_errors']));
	LogMessage("tasks", sprintf(_("Diagnosis: %d tests run with %d errors"),$diagnosis['nb_tests'],$diagnosis['nb_errors']));	
	if ($diagnosis['nb_tests']==0) {
		echo(_("ERROR: No test done during diagnosis!"));
	} else {
		if ($diagnosis['nb_errors']==0) {
			echo(_("No error detected during the diagnosis")."\n");
			echo("=> ALL_DIAGNOSIS_OK\n");
		} else {
			echo(_("ERROR: Please check the detailed diagnosis page!"));
		}
	}
	
	// End
	$ts_end=time();
	LogMessage("tasks", sprintf(_("End processing tasks in %d seconds"),($ts_end-$ts_start)));	

?>
