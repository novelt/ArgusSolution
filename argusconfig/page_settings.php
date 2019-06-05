<?php

	// Start
	require_once ("./tools/web_template.php");
	$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
	
	// Check if there are some values to be saved
	$NbSaved=0;
	foreach($_POST as $Name=>$Value) {
		if (strpos($Name, "INTEGER-")===0) {
			$Key=str_replace("INTEGER-", "", $Name);
			ConfigSetInteger ($bdd, $Key, intval(trim($Value)));
			$NbSaved++;
		}
		if (strpos($Name, "STRING-")===0) {
			$Key=str_replace("STRING-", "", $Name);
			ConfigSetString ($bdd, $Key, trim($Value));
			$NbSaved++;
		}
	}

	// Check if we display a notification
	$NotificationType="OK";
	$Notification="";
	if ($NbSaved>0) {
		$Notification=sprintf(_("%d values has been saved"), $NbSaved);
	}
	
	// Sending HTML headers and notification
	WebHeader(_("Application global parameters"),$Notification,$NotificationType);
	//DebugDump($_POST);

	// Warning
	echo('<p><span class="warning">'._("Warning!").'</span> '._("Incorrect configuration can prevent the application from working correctly").'</p>');
	echo('<p>'._("Multiple global keywords can be specified using comma \",\"").'</p>');
	
	// Read each parameter in loop
	echo('<form method="post">');
	echo('<table><tr><th>Parameter</th><th>Integer value</th><th>String value</th></tr>');
	foreach ($config_defaults as $key=>$settings) {
		if ($settings['DisplaySettings']==TRUE) {
			echo('<tr><td>' . nl2br(htmlspecialchars($settings['Description'])) . '</td>');
			if ($settings['HasInteger'] == TRUE) {
				echo('<td>');
				if (is_array($settings['ListInteger']) && count($settings['ListInteger']) > 0) {
					// select list
					$Values = array();
					foreach ($settings['ListInteger'] as $Name => $Value) {
						$Values[$Value . ": " . $Name] = $Value;
					}
					echo(ElementSelect("INTEGER-" . $key, $Values, ConfigGetInteger($bdd, $key)));
				} else {
					// input text
					echo(ElementText("INTEGER-" . $key, ConfigGetInteger($bdd, $key), 10));
				}
				echo('<br><span class="defaults">Default=' . $settings['DefaultInteger'] . '</span>');
				echo('</td>');
			} else {
				echo('<td class="disabled">' . _("No integer value for this parameter") . '</td>');
			}
			if ($settings['HasString'] == TRUE) {
				echo('<td>');
				echo(ElementText("STRING-" . $key, ConfigGetString($bdd, $key), 40));
				echo('<br><span class="defaults">Default=' . $settings['DefaultString'] . '</span>');
				echo('</td>');
			} else {
				echo('<td class="disabled">' . _("No string value for this parameter") . '</td>');
			}
			echo('</tr>');
		}
	}
	echo('</table>');
	echo('<input type="submit" value="'.htmlspecialchars(_("Save the values")).'">');
	echo('</form>');
	
	// End
	WebFooter();
	db_Close($bdd);
	
?>