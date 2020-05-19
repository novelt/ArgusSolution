<?php
	require_once("./tools/diagnosis.php");

	// Make a backup of the database
	function MySQL_run_backup() {
		global $config;
		$Messages=array();
		$Messages[]=_("Backup of the database");
		// Paths
		$BackupPath=$config["path_backup"];
		$MySQLDumpPath=$config["mysql_dump_exe_path"];
		// Checking backup path
		$PathOK=FALSE;
		if (file_exists($BackupPath) && is_dir($BackupPath)) {
			// Backup path found
			$Messages[]=sprintf(_("Backup path %s has been found"),$BackupPath);
			$PathOK=TRUE;
		} else {
			// Backup path not found
			// mkdir can't created mutiple sub-directory at once, so, the creation must be manual
			$Messages[]=sprintf(_("ERROR: Backup path %s wasn't found, backup cancelled!"),$BackupPath);
		}

		// Continue if backup path is OK
		if ($PathOK==TRUE) {
			// Backup file info

			// Test if database exist before doing a backup
			if (test_mysql_schema_exist() == true) {
				$BackupFile = $BackupPath . 'backupdb-frontlinesms-' . date('YmdHis') . '.sql';
				$Messages[] = sprintf(_("Backup file will be %s"), $BackupFile);
				// Backup execution
				$cmd = '"' . $MySQLDumpPath . '" --routines --skip-extended-insert --default-character-set=utf8 --quick --user=' . $config["mysql_user"] . ' --password=' . $config["mysql_password"] . ' --single-transaction --quote-names --triggers --comments ' . $config["mysql_db"] . ' > "' . $BackupFile . '"';
				$Messages[] = sprintf(_("Commandline is %s"), $cmd);
				unset($output);
				$exitcode = -1;
				exec($cmd, $output, $exitcode);
				// Show output
				for ($i = 0; $i < count($output); $i++) {
					$Messages[] = _("Output:") . " " . $output[$i];
				}

				// Check exit code
				if ($exitcode==0) {
					// Backup OK
					$Messages[]=_("MySQLdump has been executed without error");
					// Cleaning
					$BackupFiles=MySQL_list_backup();
					$nb=0;
					foreach ($BackupFiles as $Timestamp=>$Values) {
						$nb++;
						if (($nb>$config["backup_minimum_number"]) && (time()-$Timestamp>$config["backup_minimum_day"]*86400)) {
							$BackupFile=$Values['Path'];
							if (unlink($BackupFile)===TRUE) {
								$Messages[]=sprintf(_("Cleaning backup file %s"),$BackupFile);
							} else {
								$Messages[]=sprintf(_("ERROR: Cleaning backup file %s"),$BackupFile);
							}
						}
					}
				}
				else {
					// Erreur backup
					$Messages[]=sprintf(_("ERROR: MySQLdump exitcode=%d"),$exitcode);
				}
			}
			else {
				$Messages[]=_("First initialization, no backup possible");
			}
		}
		// Return messages
		return($Messages);
	}

	// Clean Data
	function MySQL_run_cleaning($CleaningFile){
		global $config;
		// Increase the allowed execution time
		set_time_limit (3600);
		// Return messages
		$Messages=array();
		$Messages[]=sprintf(_("Cleaning the database with file %s"),$CleaningFile);
		// Checking file
		if ($CleaningFile!="" && file_exists($CleaningFile) && is_file($CleaningFile) && filesize($CleaningFile)) {

			$MySQLPath=$config["mysql_exe_path"];

			// Cleaning execution
			$cmd='"'.$MySQLPath.'" --database='.$config["mysql_db"].' --user='.$config["mysql_user"].' --password='.$config["mysql_password"].' < "'.$CleaningFile.'"';
			$Messages[]=sprintf(_("Commandline is %s"),$cmd);
			unset($output);
			$exitcode=-1;
			exec($cmd,$output,$exitcode);
			// Show output
			for ($i=0; $i<count($output); $i++) {
				$Messages[]=_("Output:")." ".$output[$i];
			}
			// Check exit code
			if ($exitcode==0) {
				// Restore OK
				$Messages[]=_("MySQL has been executed without error");
			} else {
				// Error
				$Messages[]=sprintf(_("ERROR: MySQL exitcode=%d"),$exitcode);
			}
		} else {
			// Error bad file
			$Messages[]=_("ERROR: The cleaning file can't be used!");
		}
		// Return messages
		return($Messages);
	}

	// List avalaible backups in datetime order (more recent first)
	function MySQL_list_backup() {
		global $config;
		// List of files found
		$BackupFiles=array();
		if (file_exists($config["path_backup"]) && is_dir($config["path_backup"])) {
			$Files=scandir($config["path_backup"]);
			for ($i=0;$i<count($Files);$i++) {
				$File=$Files[$i];
				// Check file
				if ($File!="." && $File!=".." && strpos($File,"backupdb-frontlinesms-")===0 && strpos($File,".sql")===(strlen($File)-4)) {
					// Backup file found
					$Path=$config["path_backup"].$File;
					$Timestamp=filemtime($Path);
					$BackupFiles[$Timestamp]=array('Path'=>$Path,'File'=>$File);
				}
			}
		}
		// Sort by decreasing timestamp, needed for cleaning
		krsort($BackupFiles);
		return($BackupFiles);
	}


	/**
	 * Drop database if exists
	 * Create database if exists
	 *
	 * @return array
	 */
	function MySQL_init_database()
	{
		global $config;
		// Increase the allowed execution time
		set_time_limit (3600);
		// Return messages
		$Messages=array();

		$Messages[]=sprintf(_("Initializing Database"));

		$MySQLPath=$config["mysql_exe_path"];

		// Drop Database if exists
		$cmd='"'.$MySQLPath.'" --user='.$config["mysql_user"].' --password='.$config["mysql_password"].' -e "DROP DATABASE IF EXISTS '.$config["mysql_db"].'"' ;
		$Messages[]=sprintf(_("Commandline is %s"),$cmd);
		unset($output);
		$exitcode=-1;
		exec($cmd,$output,$exitcode);
		// Show output
		for ($i=0; $i<count($output); $i++) {
			$Messages[]=_("Output:")." ".$output[$i];
		}
		// Check exit code
		if ($exitcode==0) {
			// Restore OK
			$Messages[]=_("MySQL has been executed without error");
		} else {
			// Error
			$Messages[]=sprintf(_("ERROR: MySQL exitcode=%d"),$exitcode);
		}

		// Create DataBase if not exists
		$cmd='"'.$MySQLPath.'" --user='.$config["mysql_user"].' --password='.$config["mysql_password"].' -e "CREATE DATABASE IF NOT EXISTS '.$config["mysql_db"].' CHARACTER SET utf8 COLLATE utf8_general_ci"' ;
		$Messages[]=sprintf(_("Commandline is %s"),$cmd);
		unset($output);
		$exitcode=-1;
		exec($cmd,$output,$exitcode);
		// Show output
		for ($i=0; $i<count($output); $i++) {
			$Messages[]=_("Output:")." ".$output[$i];
		}
		// Check exit code
		if ($exitcode==0) {
			// Restore OK
			$Messages[]=_("MySQL has been executed without error");
		} else {
			// Error
			$Messages[]=sprintf(_("ERROR: MySQL exitcode=%d"),$exitcode);
		}

		// Return messages
		return($Messages);
	}
    /**
	 *  Run an sql file
	 *
     * @param $file
     * @return array
     */
	function MySQL_run_file($file) {
		global $config;
		// Increase the allowed execution time
		set_time_limit (3600);
		// Return messages
		$Messages=array();
		// Checking file
		if ($file!="" && file_exists($file) && is_file($file) && filesize($file)) {

			$MySQLPath=$config["mysql_exe_path"];

			// Cleaning execution
			$cmd='"'.$MySQLPath.'" --database='.$config["mysql_db"].' --user='.$config["mysql_user"].' --password='.$config["mysql_password"].' < "'.$file.'" 2>&1';
			$Messages[]=sprintf(_("Commandline is %s"),$cmd);
			unset($output);
			$exitcode=-1;
			exec($cmd,$output,$exitcode);
			// Show output
			for ($i=0; $i<count($output); $i++) {
				$Messages[]=_("Output:")." ".$output[$i];
			}
			// Check exit code
			if ($exitcode==0) {
				// Restore OK
				$Messages[]=_("MySQL has been executed without error");
			} else {
				// Error
				$Messages[]=sprintf(_("ERROR: MySQL exitcode=%d"),$exitcode);
			}
		} else {
			// Error bad file
			$Messages[]=_("ERROR: The file can't be used!");
		}
		// Return messages
		return($Messages);
	}

?>
