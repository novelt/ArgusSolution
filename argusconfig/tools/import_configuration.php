<?php
	require_once("mysql_functions.php");
	// Check database records
	function GetDatabaseRecordsCount()
    {
		$Status=_("Database records count summary:")."\r\n";

		$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
		$Value=db_Scalar ($bdd,"SELECT COUNT(*)-1 FROM frontline_group;",__FUNCTION__,__LINE__,__FILE__);
		$Status.=sprintf(_("* %d sites")."\r\n",$Value);
		$Value=db_Scalar ($bdd,"SELECT COUNT(*) FROM contact;",__FUNCTION__,__LINE__,__FILE__);
		$Status.=sprintf(_("* %d contacts")."\r\n",$Value);
		$Value=db_Scalar ($bdd,"SELECT COUNT(*) FROM ses_diseases;",__FUNCTION__,__LINE__,__FILE__);
		$Status.=sprintf(_("* %d diseases")."\r\n",$Value);
		$Value=db_Scalar ($bdd,"SELECT COUNT(*) FROM ses_thresholds;",__FUNCTION__,__LINE__,__FILE__);
		$Status.=sprintf(_("* %d thresholds"),$Value);
		db_Close($bdd);
		return($Status);
	}

	// Global import function
	function ImportConfiguration()
    {
		global $config;
		$nbFileNeededForSynch = 4;

        // At start of script
        $time_start = microtime(true);

		// Create connexion to database for the entire synch
        $bdd = db_Open(__FUNCTION__, __LINE__, __FILE__);

		try {// Check presence of files
            if (checkIfFilesExists($config["path_data_input"], '.xml', $nbFileNeededForSynch)) {
                // Files present
                // Lock Tables used to synchronize
                if (lockTablesForSynchronization($bdd)) {
                    // Recheck if files are present
                    if (checkIfFilesExists($config["path_data_input"],'.xml', $nbFileNeededForSynch)) {
                        // Move the files and do the synch
                        if (MoveFiles($config["path_data_input"], $config["path_data_work"], '.xml')) {
                            // Unlock tables
                            unLockTablesForSynchronization($bdd);
                            // Synchronisation
                            BeginProcessImportConfiguration($bdd, $config["path_data_work"], $config["path_data_processed"], $config["path_import_schema"]);
                        } else {
                            LogMessage("tasks", sprintf(_("Error in ImportConfiguration when processing the importation")));
                            echo(sprintf(_("Error in ImportConfiguration when processing the importation"))."\n");
                        }
                    }
                } else {
                    LogMessage("tasks", sprintf(_("Error in ImportConfiguration when trying to lock tables")));
                    echo(sprintf(_("Error in ImportConfiguration when trying to lock tables"))."\n");
                }
            } else {
                echo(sprintf(_("All %d XML Synchronization files not found in [%s]"), $nbFileNeededForSynch, $config["path_data_input"])."\n");
            }
        } catch (Exception $ex) {
            LogMessage("tasks", sprintf(_("Exception catched in ImportConfiguration function %s"),$ex));
            echo(sprintf(_("Exception catched in ImportConfiguration function %s"),$ex)."\n");

        } finally {
            unLockTablesForSynchronization($bdd);
            db_Close($bdd);
        }

        // Anywhere else in the script
        echo 'Total execution time in seconds: ' . (microtime(true) - $time_start) ."\n";
	}

    /**
     * Synchonisation process
     *
     * @param $bdd
     * @param $workFolder
     * @param $processFolder
     * @param $importSchemaPath
     */
	function BeginProcessImportConfiguration($bdd, $workFolder, $processFolder, $importSchemaPath)
    {
        // Declare variables containing all our data
        $importData = array();
        $importData['sites'] = array();
        $importData['contacts'] = array();
        $importData['diseases'] = array();
        $importData['thresholds'] = array();

        $Files = scandir($workFolder);
		$NbImported=0;
        $allErrors = array();

		for ($i=0; $i<count($Files); $i++) {
			$FileName=$workFolder.$Files[$i];
			if (is_file($FileName) && strpos(strtolower($FileName),".xml")>0) {
				// File to import
				$NbImported++;

				echo(sprintf(_("New XML file [%s] found!"),$FileName)."\n");
				echo(sprintf(_("Trying to import with schema [%s]..."),$importSchemaPath)."\n");
				$errors_list = CheckConfigurationFile($bdd, $FileName, $importSchemaPath, $importData);

				// Merge errors
                $allErrors = array_merge($allErrors, $errors_list);

				// Check for errors
				$Timestamp=date("Ymd-His");

				if (count($errors_list)>0) {
					// Errors
					$ErrorFileName=$processFolder.$Timestamp."-ERROR-".$Files[$i].".txt";
					echo(sprintf(_("%d ERRORS were found during import and saved in file [%s]")."\n", count($errors_list), $ErrorFileName));
					for ($j=0; $j<count($errors_list); $j++) {
						file_put_contents($ErrorFileName, $errors_list[$j]."\r\n", FILE_APPEND);
					}
					LogMessage("tasks", sprintf(_("%d errors while importing file %s"),count($errors_list),$Files[$i]));
				} else {
					echo(_("No error found when reading the file")."\n");
					LogMessage("tasks", sprintf(_("File %s was read"), $Files[$i]));
				}
				// Backup the file
				$BackupFileName=$processFolder.$Timestamp."-".$Files[$i];
				rename($FileName, $BackupFileName);
				echo(sprintf(_("The imported file has been moved to [%s]"),$BackupFileName)."\n");
			}
		}

        echo("\n");

		if (count($allErrors) == 0) {
            // OK, Start Importing data
            echo("\n");
            if (ImportConfigurationData($bdd, $importData)) {
                // Display the number of files imported
                echo(sprintf(_("%d files has been processed"),$NbImported)."\n");
            } else {
                echo(sprintf("/!\\/!\\/!\\/!\\/!\\/!\\/!\\/!\\/!\\")."\n");
                echo(sprintf(_("DATA HAVE NOT BEEN IMPORTED DUE TO MYSQL ERRORS"))."\n");
                echo(sprintf(_("Please check the error log files"))."\n");
            }

        } else {
            echo(sprintf("/!\\/!\\/!\\/!\\/!\\/!\\/!\\/!\\/!\\")."\n");
            echo(sprintf(_("DATA HAVE NOT BEEN IMPORTED DUE TO ERRORS IN FILES"))."\n");
        }

        echo("\n");
    }

    /**
     * Move All files from $folderSource to $folderDestination
     *
     * @param $folderSource
     * @param $folderDestination
     * @param $extension
     * @return bool
     */
	function MoveFiles($folderSource, $folderDestination, $extension)
    {
        $files = scandir($folderSource);

        for ($i=0; $i < count($files); $i++) {
            $fileName=$folderSource.$files[$i];
            if (is_file($fileName) && strpos(strtolower($fileName),$extension) > 0) {
                $newFileName = $folderDestination . $files[$i];
                rename($fileName, $newFileName);
            }
        }

        return true ;
    }

    /**
     * Check if files exists in the specified folder
     *
     * @param $folder
     * @param $extension
     * @param $nbFileNeeded
     * @return bool
     */
	function checkIfFilesExists($folder, $extension, $nbFileNeeded)
    {
        $files = scandir($folder);
        $counter = 0;

        for ($i=0; $i<count($files); $i++) {
            $fileName=$folder.$files[$i];
            if (is_file($fileName) && strpos(strtolower($fileName),$extension) > 0) {
                $counter ++;
            }
        }

        return $counter == $nbFileNeeded ;
    }

    /**
     * Lock tables for synch
     *
     * @param $bdd
     * @return bool
     */
	function lockTablesForSynchronization($bdd)
    {
        $tables = [];
        $tables[] = 'frontline_group' ;
        $tables[] = 'contact' ;
        $tables[] = 'ses_recipients' ;
        $tables[] = 'groupmembership' ;
        $tables[] = 'ses_diseases' ;
        $tables[] = 'ses_disease_values' ;
        $tables[] = 'ses_disease_constraints' ;
        $tables[] = 'ses_thresholds' ;
        $tables[] = 'ses_nvc' ;

        return db_Lock($bdd,__FUNCTION__, __LINE__, __FILE__, $tables, 'WRITE');
    }

    /**
     * Unlock tables for synch
     *
     * @param $bdd
     * @return bool
     */
    function unLockTablesForSynchronization($bdd)
    {
        return db_Unlock($bdd, __FUNCTION__, __LINE__, __FILE__);
    }

    /**
     * Read the XML configuration, analyze-it and save it if possible
     *
     * @param $bdd
     * @param $XmlFile
     * @param $XsdFile
     * @param $importData
     * @return array
     */
	function CheckConfigurationFile($bdd, $XmlFile, $XsdFile, &$importData)
    {
		require_once("xml_functions.php");
		// List of errors
		$errors_list=array();
		// Validate the schema
		$validation_errors=xml_validate($XmlFile, $XsdFile);
		if (count($validation_errors)==0) {
			// Validation OK
			// Load the XML document
			$DOM=new DOMDocument();
			$DOM->load($XmlFile);
			// Check the root node
			if ($DOM->childNodes->length==1 && $DOM->childNodes->item(0)->nodeName=="import") {
				// List the main configuration nodes
				foreach ($DOM->childNodes->item(0)->childNodes as $MainNode) {
					switch ($MainNode->nodeName) {
						case "sites":
							require_once("import_sites.php");
							$import_errors=CheckSites($bdd, $MainNode, $importData['sites']);
							for ($i=0; $i<count($import_errors); $i++) {
								$errors_list[]=$import_errors[$i];
							}
							break;
						case "contacts":
							require_once("import_contacts.php");
							$import_errors=CheckContacts($bdd, $MainNode, $importData['contacts'], $importData['sites']);
							for ($i=0; $i<count($import_errors); $i++) {
								$errors_list[]=$import_errors[$i];
							}
							break;
						case "diseases":
							require_once("import_diseases.php");
							$import_errors=CheckDiseases($bdd, $MainNode, $importData['diseases']);
							for ($i=0; $i<count($import_errors); $i++) {
								$errors_list[]=$import_errors[$i];
							}
							break;
						case "thresholds":
							require_once("import_thresholds.php");
							$import_errors=CheckThresholds($bdd, $MainNode, $importData['thresholds'], $importData['sites'], $importData['diseases']);
							for ($i=0; $i<count($import_errors); $i++) {
								$errors_list[]=$import_errors[$i];
							}
							break;
					}
				}
			} else {
				// Root node not found
				$errors_list[]=_("ERROR ImportConfiguration: the <import> XML root node was not found!");
			}
		} else {
			// Validation errors, we stop here
			$errors_list[]=sprintf(_("ERROR ImportConfiguration: the XML file [%s] failed the validation with the schema [%s]!"), $XmlFile, $XsdFile);
			for ($i=0; $i<count($validation_errors); $i++) {
				$errors_list[]=$validation_errors[$i];
			}
		}
		// Return the errors list
		return($errors_list);
	}

    /**
     * Import Configuration data
     *
     * @param $bdd
     * @param $importData
     *
     * @return bool
     */
	function ImportConfigurationData($bdd, $importData)
    {
        require_once("import_sites.php");
        require_once("import_contacts.php");
        require_once("import_diseases.php");
        require_once("import_thresholds.php");

        try {
            // Start Transaction
            db_StartTransaction($bdd, __FUNCTION__, __LINE__, __FILE__);

            // Delete all old data
            RemoveOldData($bdd);

            //Import data
            ImportSites($bdd, $importData['sites']);
            ImportContacts($bdd, $importData['contacts'], $importData['sites']);
            ImportDiseases($bdd, $importData['diseases']);
            ImportThresholds($bdd, $importData['thresholds'], $importData['sites']);

            // COMMIT TRANSACTION
            db_CommitTransaction($bdd, __FUNCTION__, __LINE__, __FILE__);

        } catch (Exception $ex) {
            // ROLLBACK TRANSACTION
            db_RollbackTransaction($bdd, __FUNCTION__, __LINE__, __FILE__);
            LogMessage("errors", sprintf(_("An error occurs when importing data : %s"), $ex->getMessage()));

            return false;
        }

        return true ;
    }

    /**
     * Remove all old data before importing the new configuration
     *
     * @param $bdd
     */
    function RemoveOldData($bdd)
    {
        // Delete sites/contacts relationship
        db_Query($bdd, "DELETE FROM groupmembership;", __FUNCTION__,__LINE__,__FILE__, false);
        // Delete contacts
        db_Query($bdd, "DELETE FROM contact;", __FUNCTION__,__LINE__,__FILE__, false);
        // Delete recipients
        db_Query($bdd, "DELETE FROM ses_recipients;", __FUNCTION__,__LINE__,__FILE__, false);
        // Delete thresholds
        db_Query($bdd, "DELETE FROM ses_thresholds;", __FUNCTION__,__LINE__,__FILE__, false);
        // Delete sites (excepted the root site)
       // db_Query($bdd, "DELETE FROM frontline_group WHERE path<>'/SitesRoot';", __FUNCTION__,__LINE__,__FILE__);
        db_Query($bdd, "DELETE FROM frontline_group;", __FUNCTION__,__LINE__,__FILE__, false);

        // Delete thresholds
        db_Query($bdd, "DELETE FROM ses_thresholds;", __FUNCTION__, __LINE__, __FILE__, false);
        // Delete values
        db_Query($bdd, "DELETE FROM ses_disease_values;", __FUNCTION__, __LINE__, __FILE__, false);
        // Delete constraints
        db_Query($bdd, "DELETE FROM ses_disease_constraints;", __FUNCTION__, __LINE__, __FILE__, false);
        // Delete diseases
        db_Query($bdd, "DELETE FROM ses_diseases;", __FUNCTION__, __LINE__, __FILE__, false);
    }
?>