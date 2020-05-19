<?php

	// Start
	require_once ("./tools/web_template.php");
	require_once ("./tools/string_functions.php");
	$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);

	// Header
	WebHeader(_("List of received reports"));

	// Getting reports
	$tableFields = 'd.id, d.path, d.reception, d.exported, d.contactName, d.contactPhoneNumber, d.disease, d.period, d.periodStart, d.reportId';
	$SQL="SELECT " . $tableFields . ", group_concat(v.key SEPARATOR '|') AS dataKeys, group_concat(v.value SEPARATOR '|') AS dataValues FROM ses_data d INNER JOIN ses_datavalues v ON d.id = v.FK_dataId GROUP BY d.id ORDER BY reception DESC LIMIT 1000;";
	$reports=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
	if (count($reports)>0) {
		echo('<p>'.sprintf(_("%d reports and alerts found and sorted as newer first: (display limited to 1000 records)"),count($reports)).'</p>');
		echo('<table>');
		echo('<tr><th>'._("Reception").'</th><th>'._("Exported").'</th><th>'._("Site").'</th><th>'._("Contact").'</th><th>'._("Number").'</th><th>'._("Disease").'</th><th>'._("Period").'</th><th>'._("Start").'</th><th>'._("Values").'</th><th>'._("Report ID").'</th></tr>');
		for ($i=0; $i<count($reports); $i++) {
			echo('<tr>');
			echo('<td>'.date("d/m/Y H:i:s",strtotime($reports[$i]['reception'])).'</td>');
			if (isset($reports[$i]['exported'])==TRUE) {
				echo('<td class="green">'.date("d/m/Y H:i:s",strtotime($reports[$i]['exported'])).'</td>');
			} else {
				echo('<td class="red">'._("No").'</td>');
			}
			echo('<td>'.htmlspecialchars(GetSiteReferenceFromPath($reports[$i]['path'])).'</td>');
			echo('<td>'.htmlspecialchars($reports[$i]['contactName']).'</td>');
			echo('<td>'.htmlspecialchars($reports[$i]['contactPhoneNumber']).'</td>');
			echo('<td>'.htmlspecialchars($reports[$i]['disease']).'</td>');
			echo('<td>'.htmlspecialchars($reports[$i]['period']).'</td>');
			echo('<td>'.date("d/m/Y",strtotime($reports[$i]['periodStart'])).'</td>');
			$keys = explode('|', $reports[$i]['dataKeys']);
			$values = explode('|', $reports[$i]['dataValues']);
            $results = "";
			for ($j=0; $j< count($keys); $j++) {
                if ($results!="") {
                    $results.=", ";
                }
                $results .=$keys[$j]."=".$values[$j];
			}
			echo('<td>'.htmlspecialchars($results).'</td>');
			echo('<td>'.htmlspecialchars($reports[$i]['reportId']).'</td>');
			echo('</tr>');
		}
		echo('</table>');
	} else {
		// No reports
		echo('<p>'._("No reports yet received").'</p>');
	}

	// End
	WebFooter();
	db_Close($bdd);

?>
