<?php

	// Complete application initialization
	function initialize_application()
	{
		global $config;
		require_once("backup_functions.php");
		$Messages=array();
		// Backup
		$Messages[]=_("Step #1 - Make a backup");
		$Messages=array_merge($Messages,MySQL_run_backup());
		// Clean log files
		$Messages[]=_("Step #2 - Remove log files");
		$Files=scandir($config["path_logs"]);
		for ($i=0;$i<count($Files);$i++) {
			if (stripos($Files[$i],'.txt')!==false) {
				$LogFileName=$config["path_logs"].$Files[$i];
				unlink($LogFileName);
				$Messages[]=sprintf(_("The log file %s has been removed"),$LogFileName);
			}
		}

		/*
		 * WARNING: be careful with the steps order
		 * Update the configuration files AFTER having run the SQL initialization scripts:
		 *  - Step #3 - Restore an empty database
		 *  - Step #4 - Update configuration files
		 * --> Do NOT invert these two steps.
		 *
		 * This is related to the time zones in MySQL. We have to fill the time_zone* tables in the database 'mysql'
		 * before setting the parameter 'default-time-zone' in the my.ini file. Otherwise, MySQL will fail to start.
		 * If in the MySQL logs (mysql_error.log) you see: "Fatal error: Illegal or unknown default time zone 'xxx'":
		 *  - comment the setting 'default-time-zone' in the my.ini file
		 *  - restart MySQL
		 *  - run the script Timezones.sql in the Install folder
		 *  - stop MySQL
		 *  - uncomment the setting 'default-time-zone' in the my.ini file
		 *  - start MySQL
		 * */

        // Restore default database
        $Messages[]=_("Step #3 - Restore an empty database");

        // Drop or create old database
        $Messages=array_merge($Messages,MySQL_init_database());

        // Restore All files in the Install folders and subfolders
        $Messages=array_merge($Messages,run_install_scripts("Schema"));

		if ($config['php_os'] === 'win')
		{
			// Copy configuration files
			$Messages[]=_("Step #4 - Update configuration files");
			if (copy($config["path_ressources"]."config_apache.txt", $config["apache_conf_path"])) {
				$Messages[]=_("Apache configuration copied");
			} else {
				$Messages[]=_("ERROR while copying Apache configuration!");
			}
			if (copy($config["path_ressources"]."config_php.txt", $config["php_conf_path"])) {
				$Messages[]=_("PHP configuration copied");
			} else {
				$Messages[]=_("ERROR while copying PHP configuration!");
			}
			if (copy($config["path_ressources"]."config_mysql.txt", $config["mysql_conf_path"])) {
				$Messages[]=_("MySQL configuration copied");
			} else {
				$Messages[]=_("ERROR while copying MySQL configuration!");
			}
		}

		// End
		$Messages[]=_("THE END - Please restart Apache, MySQL and FrontlineSMS!");
		return($Messages);
	}

	// Cleaning application
	function clean_application()
	{
		global $config;
		require_once("backup_functions.php");
		$Messages=array();
		// Backup
		$Messages[]=_("Step #1 - Make a backup");
		$Messages=array_merge($Messages,MySQL_run_backup());

		// Clean database
		$Messages[]=_("Step #2 - Clean database");
		$Messages=array_merge($Messages,MySQL_run_cleaning(($config["path_ressources_database"]."database_cleaning.sql")));
		// End
		return($Messages);
	}

	/**
	 * Delete entries linked to argus gateway devices
	 *
	 * @return array
	 */
	function clean_argus_gateway_devices()
	{
		global $config;
		require_once("backup_functions.php");
		$Messages=array();
		// Backup
		$Messages[]=_("Step #1 - Make a backup");
		$Messages=array_merge($Messages,MySQL_run_backup());

		// Clean database
		$Messages[]=_("Step #2 - Clean Argus Gateway Devices");
		$Messages=array_merge($Messages,MySQL_run_cleaning(($config["path_ressources_database"]."database_argus_devices_cleaning.sql")));
		// End
		return($Messages);
	}

	/**
	 * Run all scripts in the Install folder of this version
	 *
	 * @param $installFolder
	 * @return array
	 */
	function run_install_scripts($installFolder)
	{
		global $config;
		require_once("backup_functions.php");
		$Messages=array();

		$Messages[]="---------------------" ;
		$Messages[]="Folder ".$installFolder ;

		foreach($config["path_ressources_database_initialization_folders"] as $folderName) {
            // List all files in the current folder
		    $Messages=array_merge($Messages, run_all_scripts_depth($config["path_ressources_database"] . $installFolder .DIRECTORY_SEPARATOR . $folderName));
        }
        $Messages[] = "Initialization scripts in folder ".$installFolder." have been run";
        $Messages[]="---------------------";

		return $Messages ;
	}

   /**
	* Update the database regarding the schema version defnied in the config settings file
    *
    * @param string $targetDatabaseFolder Run the scripts until the specified database folder is found (folder included in the update).
	*
    * @return array
	*/
	function update_application($targetDatabaseFolder = null){
		global $config;
		require_once("backup_functions.php");
		$Messages=array();

		// Check version
		$Messages[]=_("Step #1 - Check the version of the application");
		$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
		$current_version = getLastSchemaVersion($bdd);
		db_Close($bdd);

		$app_version = $config["schema_version"];

		$Messages[]=_("Current version of the database schema : ".$current_version);
		$Messages[]=_("Version of the Application : ".$app_version);

		if (strnatcmp($current_version , $app_version) < 0) {
			$Messages[]=_("Application Need to be updated !");
		} else {
			$Messages[]=_("No update needed ");
			$Messages[]=_("END");
			return ($Messages);
		}

		// Backup
		$Messages[]=_("Step #2 - Make a backup");
		//$Messages=array_merge($Messages,MySQL_run_backup());

		// Check which files have to be run
		$Messages[]=_("Step #3 - Find sql update files");
		$databaseFolders = scandir($config["path_ressources_database"]);

		// Sort database folders natural order (http://php.net/manual/fr/function.natsort.php)
		natsort($databaseFolders);

        $foldersPresent = getDatabaseUpdateFolders(true);
		$updateFolders = array() ;

		$folderNameFound = false;
		$i = 0;

		while(!$folderNameFound && $i < sizeof($foldersPresent)) {
		    $folder = $foldersPresent[$i];

            if($folder['run'] === true) {
                $updateFolders[] = $folder['folderName'];
            }

            $folderNameFound = ($folder['folderName'] == $targetDatabaseFolder);
            $i++;
        }

		$Messages = array_merge($Messages, run_update_scripts($updateFolders)) ;
		$Messages[] = "END of the update";

		// End
		return($Messages);
	}

    /**
     * @param bool $includeOnlyFoldersToBeRun
     * @return array
     */
	function getDatabaseUpdateFolders($includeOnlyFoldersToBeRun) {
        global $config;

        $databaseFolders = scandir($config["path_ressources_database"]);
        $app_version = $config["schema_version"];

        $bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
        $current_version = getLastSchemaVersion($bdd);
        db_Close($bdd);

        // Sort database folders natural order (http://php.net/manual/fr/function.natsort.php)
        natsort($databaseFolders);

        // list all folders to update the database.
        $updateFolders = array() ;

        foreach ($databaseFolders as $databaseFolder) {
            if ($databaseFolder === '.' or $databaseFolder === '..') continue;

            if (is_dir($config["path_ressources_database"] . $databaseFolder)) {
                $versionFolder = substr($databaseFolder, 1);

                // http://php.net/manual/fr/function.strnatcmp.php
                if ( strnatcmp($versionFolder, $current_version) <= 0) {
                    if($includeOnlyFoldersToBeRun) {
                        $updateFolders[] = [
                            'folderName' => $databaseFolder,
                            'run' => false,
                            'message' => _($databaseFolder . " will not be run")
                        ];
                    }
                }
                else {
                    if (strnatcmp($app_version, $versionFolder) >= 0) {
                        $updateFolders[] = [
                            'folderName' => $databaseFolder,
                            'run' => true,
                            'message' => _($databaseFolder . " has to be run")
                        ];

                    } else if($includeOnlyFoldersToBeRun) {
                        $updateFolders[] = [
                            'folderName' => $databaseFolder,
                            'run' => false,
                            'message' => _($databaseFolder." not applicable")
                        ];
                    }
                }
            }
        }

        return $updateFolders;
    }

   /**
	*  Run all Update scripts in each Update sub folder
	*
    * @param $updateFolders
    * @return array
    */
	function run_update_scripts($updateFolders)
	{
		global $config;
		require_once("backup_functions.php");
		$Messages=array();

		$Messages[]="---------------------" ;

		foreach($updateFolders as $updateFolder) {
			$Messages[]="Folder ".$updateFolder ;

			// List all files in the Update folder
			$directoryUpdate = $config["path_ressources_database"] . $updateFolder . "/Update";

			$Messages=array_merge($Messages, run_all_scripts_depth($directoryUpdate));

			$Messages[] = "Update scripts in Folder ".$updateFolder." have been run";
			# Update the version of scripts updated
			$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
			$versionFolder = substr($updateFolder, 1);
			setLastSchemaVersion($bdd, $versionFolder);
			db_Close($bdd);

			$Messages[]="---------------------" ;
		}

		return $Messages ;
	}

    /**
	 * Run All scripts in the specified directory recursively
	 *
     * @param $directory
     * @return array
     */
	function run_all_scripts_depth($directory)
	{
		$Messages=array();

		if (is_dir($directory)) {
			$files = scandir($directory);

			natsort($files);

			foreach ($files as $file) {
				if ($file === '.' or $file === '..') continue;

				if (is_dir($directory ."/" . $file)){
					$Messages = array_merge($Messages, run_all_scripts_depth($directory . "/" . $file));
				} else {

					$fileInfo = pathinfo($file);

					if ($fileInfo['extension'] == 'sql') {
						$Messages[] = "Running File  " . $file;
						// Run this file
						$Messages = array_merge($Messages, MySQL_run_file($directory . "/" . $file));
					}
				}
			}
		}

		return $Messages ;
	}
?>
