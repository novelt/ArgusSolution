<?php
	require_once ("./tools/web_template.php");
	require_once ("./tools/backup_functions.php");
    require_once("./tools/initialization_functions.php");
	WebHeader(_("Application setup and maintenance"));

	// Get the action
	$Messages=array();
	$action=GetStringParameter("action", "");
	
	// Make backup
	if ($action=="backupdb") {
		$Messages=MySQL_run_backup();
	}

	// Clean data
	if ($action=="cleandb") {
		$Messages=clean_application();
	}

	// Clean Argus Gateway Devices
	if ($action == "clean_gateway_devices"){
		$Messages=clean_argus_gateway_devices();
	}

	// Application initialization
	if ($action=="initialize") {
		$Messages=initialize_application();
	}

	// Application update
	if ($action=="update") {
        $databaseFolderUpdateName=GetStringParameter("databaseFolderUpdateName", null);
	    $Messages=update_application($databaseFolderUpdateName);
	}
	
	// Restore database
	if ($action=="restoredb-file" || $action=="restoredb-local") {
		// Get the file
		$BackupFile="";
		if ($action=="restoredb-local") {
			// Get local backup filename
			$file=GetStringParameter("extra", "");
			if ($file!="") {
				$BackupFile=$config["path_backup"].$file;
			}
		}
		if ($action=="restoredb-file") {
			// Save the uploaded file
			if (isset($_FILES['sql_file']) && intval($_FILES['sql_file']['error'])===0 && intval($_FILES['sql_file']['size'])>0) {
				$TempFile=$config["path_backup"]."upload-restore.sql";
				if (rename($_FILES['sql_file']['tmp_name'],$TempFile)===TRUE) {
					$BackupFile=$TempFile;
				} else {
					$Messages[]=_("ERROR: Can't move the uploaded file!");
				}
			} else {
				$Messages[]=_("ERROR: Can't get the uploaded file!");
			}
		}
		// Restore
		if ($BackupFile!="") {
			$Messages = array_merge($Messages, MySQL_init_database());
			$Messages = array_merge($Messages, MySQL_run_file($BackupFile));

		} else {
			$Messages[]=_("ERROR: No backup file specified!");
		}
	}
	
	// Make a button with a warning message (confirmation)
	function HtmlSubmitButton($Label,$Action,$Confirmation,$Extra='',$additionalHTML = null) {
		$HTML='';
		if ($Confirmation==TRUE) {
			$HTML.='<form method="POST" onsubmit="return confirm(\''._("WARNING! Do you confirm the operation?").'\');">';
		} else {
			$HTML.='<form method="POST">';
		}
		$HTML.='<input type="hidden" name="action" value="'.htmlspecialchars($Action).'">';
		$HTML.='<input type="hidden" name="extra" value="'.htmlspecialchars($Extra).'">';
		$HTML.='<input type="submit" value="'.htmlspecialchars($Label).'">';

		if($additionalHTML !== null) {
            $HTML.=$additionalHTML;
        }

		$HTML.='</form method="POST">';
		return($HTML);
	}

	function HtmlDatabaseFoldersDropDownList() {
        $folders = getDatabaseUpdateFolders(false);

        if(sizeof($folders) === 0) {
            return null;
        }

	    $HTML='&nbsp;&nbsp;';

        $HTML.= '<select name="databaseFolderUpdateName">';

        $nbOfFolders = sizeof($folders);
        $i = 1;
        foreach ($folders as $folder) {
            $selected = ($i == $nbOfFolders ? 'selected' : '');
            $HTML.= '<option value="'.$folder['folderName'].'" '.$selected.'>'.$folder['folderName'].'</option>';
            $i++;
        }

        $HTML.= '</select>';


        return($HTML);
    }
	
	// Display messages
	if (count($Messages)>0) {
		echo('<p>'._("Results of the action:").'</p>');
		echo('<pre class="raw_output">');
		for ($i=0; $i<count($Messages); $i++) {
			echo(htmlspecialchars($Messages[$i]));
			if ($i<count($Messages)-1) echo("\n");
		}
		echo('</pre>');
	}
	
	// Warning
	echo('<p><span class="warning">'._("Warning!").'</span> '._("Some maintenance operations can delete all the data stored!").'</p>');
	
	// Maintenance
	echo('<div class="title1">'._("Maintenance operations").'</div>');
	echo('<table>');
	echo('<tr><th>'._("Description").'</th><th>'._("Action").'</th></tr>');
	echo('<tr><td>'._("Make a local backup of the MySQL database").'</td><td>'.HtmlSubmitButton(_("Backup"),'backupdb',FALSE).'</td></tr>');
	echo('<tr><td>'._("Delete all received dashboard data").' <b style="color:red;">'._("ALL THE DATA WILL BE DELETED!").'</b></td><td>'.HtmlSubmitButton(_("Clean"),'cleandb',TRUE).'</td></tr>');
	echo('<tr><td>'._("Delete all Argus Gateway devices").'</td><td>'.HtmlSubmitButton(_("Clean Argus Gateway devices"),'clean_gateway_devices',TRUE).'</td></tr>');
	//echo('<tr><td>'._("Create the shortcuts for the application").'</td><td>'.HtmlSubmitButton(_("Shortcuts"),'shortcuts',FALSE).'</td></tr>');
	echo('<tr><td>'._("Application initialization:").' <b style="color:red;">'._("ALL THE DATA WILL BE DELETED!").'</b></td><td>'.HtmlSubmitButton(_("Initialize"),'initialize',TRUE).'</td></tr>');
	echo('<tr><td>'._("Application update").'</td><td>'.HtmlSubmitButton(_("Update"),'update',TRUE, '', HtmlDatabaseFoldersDropDownList()).'</td></tr>');
	echo('</table>');

	// MySQL restore database
	echo('<div class="title1">'._("MySQL database restore from local backups").' - <span style="color:red;">'._("ALL DATA WILL BE REPLACED!").'</span></div>');
	$BackupFiles=MySQL_list_backup();
	if (count($BackupFiles)>0) {
		// List of backups
		echo('<table>');
		echo('<tr><th>'._("Date").'</th><th>'._("Time").'</th><th>'._("File").'</th><th>'._("Action").'</th></tr>');
		foreach ($BackupFiles as $Timestamp=>$Values) {
			echo('<tr>');
			echo('<td>'.date("d/m/Y",$Timestamp).'</td>');
			echo('<td>'.date("H:i:s",$Timestamp).'</td>');
			echo('<td>'.htmlspecialchars($Values['Path']).'</td>');
			echo('<td>'.HtmlSubmitButton(_("Restore"),'restoredb-local',TRUE,$Values['File']).'</td>');
			echo('</tr>');
		}
		echo('</table>');
	} else {
		// No local backups
		echo('<p>'._("No local backup found!").'<p>');
	}
	
	// Restore from backup file
	echo('<form method="POST" enctype="multipart/form-data" onsubmit="return confirm(\''._("WARNING! Do you confirm the operation?").'\');">');
	echo('<div class="title1">'._("MySQL database restore from file").' - <span style="color:red;">'._("ALL DATA WILL BE REPLACED!").'</span></div>');
	echo('<table><tr>');
	echo('<th><label for="sql_file">'._("SQL backup file:").'</label></th>');
	echo('<td><input type="file" name="sql_file" value=""></td>');
	echo('<td><input type="hidden" name="action" value="restoredb-file">');
	echo('<input type="submit" value="'._("Restore").'"></td>');
	echo('</tr></table>');
	echo('</form>');
	
	WebFooter();
?>