<?php

	// Start
	require_once ("./tools/web_template.php");
	require_once ("./tools/string_functions.php");
	$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);

	// No filter
	$Where="1=1";
	$FilterLabel="";
	
	// Filter by site
	$filter_site=GetStringParameter("filter_site","");
	if ($filter_site!="") {
		if ($Where!="") $Where.=" AND ";
		$Where.=" path='".$bdd->escape($filter_site)."' ";
		if ($FilterLabel!="") $FilterLabel.=_(" and ");
		$FilterLabel.=" ".sprintf(_("for site %s"), GetSiteReferenceFromPath($filter_site));
	}
	
	// Sending HTML headers and notification
	WebHeader(_("Thresholds list").$FilterLabel);	
	
	// Get thresholds
	$SQL="
		SELECT 
			path,
			disease,
			period,
			weekNumber,
			monthNumber,
			year,
			`maxValue`,
			`values`
		FROM 
			ses_thresholds
		WHERE 
			".$Where."
		ORDER BY
			year ASC,
			weekNumber,
			monthNumber,
			period ASC,
			path ASC,
			disease ASC
	;";
	$Thresholds=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
	if (count($Thresholds)>0) {
		echo('<p>'.sprintf(_("%d thresholds found:"),count($Thresholds)).'</p>');
		// Display the results
		echo('<table>');
		echo('<tr><th>'._("Period").'</th><th>'._("Year").'</th><th>'._("Month").'</th><th>'._("Week").'</th><th>'._("Site").'</th><th>'._("Disease").'</th><th>'._("Condition").'</th></tr>'); // <th>'._("Alert").'</th></tr>');
		for ($i=0; $i<count($Thresholds); $i++) {
			echo('<tr>');
			if ($Thresholds[$i]['period']=='Weekly') {
				echo('<td>'._("Weekly").'</td>');
			} elseif ($Thresholds[$i]['period']=='Monthly') {
				echo('<td>'._("Monthly").'</td>');
			} else {
				echo('<td>'._("Unknown").'</td>');
			}
			echo('<td>'.$Thresholds[$i]['year'].'</td>');
			echo('<td>'.$Thresholds[$i]['monthNumber'].'</td>');
			echo('<td>'.$Thresholds[$i]['weekNumber'].'</td>');
			echo('<td>'.htmlspecialchars(GetSiteReferenceFromPath($Thresholds[$i]['path'])).'</td>');
			echo('<td>'.htmlspecialchars($Thresholds[$i]['disease']).'</td>');
			echo('<td>'.htmlspecialchars($Thresholds[$i]['values'].' >= '.$Thresholds[$i]['maxValue']).'</td>');

			echo('</tr>');
		}
		echo('</table>');
	} else {
		// No thresholds!
		echo('<p>'._("No threshold found, please do an import!").'</p>');
	}
	
	// End
	WebFooter();
	db_Close($bdd);
	
?>