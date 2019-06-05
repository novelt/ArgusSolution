<?php

	// Start
	require_once ("./tools/web_template.php");
	require_once ("./tools/string_functions.php");
	$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);

	// Sending HTML headers and notification
	WebHeader(_("Sites list"));	
	
	// Get sites list
	$SQL="
		SELECT 
			fg.path, 
			fg.ses_name, 
			fg.ses_reminderWeekly, 
			fg.ses_reminderMonthly,
			fg.ses_alert_preferred_gateway,
			fg.cascading_alert,
			COUNT(DISTINCT(c.contact_id)) As 'nb_contacts',
			(SELECT COUNT(*) FROM ses_thresholds t WHERE t.path = fg.path) As 'nb_thresholds',
			IFNULL(GROUP_CONCAT(DISTINCT(rr.pathRecipients) SEPARATOR '|'),'') As 'ReportRecipients',
			IFNULL(GROUP_CONCAT(DISTINCT(ra.pathRecipients) SEPARATOR '|'),'') As 'AlertRecipients',
			IFNULL(GROUP_CONCAT(DISTINCT(rt.pathRecipients) SEPARATOR '|'),'') As 'ThresholdRecipients'
		FROM 
			frontline_group fg
			LEFT JOIN groupmembership gm ON fg.path=gm.group_path
			LEFT JOIN contact c ON (gm.contact_contact_id=c.contact_id AND c.active=1)
			LEFT JOIN ses_recipients rr ON (fg.path=rr.path AND rr.messageType='REPORT')
			LEFT JOIN ses_recipients ra ON (fg.path=ra.path AND ra.messageType='ALERT')
			LEFT JOIN ses_recipients rt ON (fg.path=rt.path AND rt.messageType='THRESHOLD')
		WHERE
			fg.path<>'/SitesRoot'
		GROUP BY
			fg.path, 
			fg.ses_name, 
			fg.ses_reminderWeekly, 
			fg.ses_reminderMonthly 
	;";
	$RawSites=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
	if (count($RawSites)>0) {
		echo('<p>'.sprintf(_("%d sites found:"),count($RawSites)).'</p>');
		// Get the list with padded references for sorting and keeping the levels
		$Sites=array();
		for ($i=0; $i<count($RawSites); $i++) {
			// Get site info
			$Reference = GetSiteReferenceFromPath($RawSites[$i]['path']);
			$Levels    = GetSiteHierarchyFromPath($RawSites[$i]['path']);
			// Create padded references for display and sorting
			$ReferenceDisplay=htmlspecialchars($Reference);
			$ReferenceSorting="";
			for ($j=0; $j<count($Levels); $j++) {
				// For display
				if ($j>0) $ReferenceDisplay='&nbsp;&nbsp;'.$ReferenceDisplay;
				// For sorting
				$ReferenceSorting.=str_pad($Levels[$j],60,' ',STR_PAD_RIGHT);
			}
			// Save the site
			$RawSites[$i]['ReferenceDisplay']=$ReferenceDisplay;
			$Sites[$ReferenceSorting]=$RawSites[$i];
		}
		// Sort padded references
		ksort($Sites);
		// Display the results
		echo('<table>');
		echo('<tr><th>'._("Reference").'</th><th>'._("Name").'</th><th>'._("Weekly reminder (min)").'</th><th>'._("Monthly reminder (min)").'</th><th>'._("Active contacts").'</th><th>'._("Thresholds").'</th><th>'._("Alert recipients").'</th><th>'._("Alert preferred Gateway").'</th></tr>'); //<th>'._("Threshold recipients").'</th>
		foreach($Sites as $ReferenceSorting=>$Site) {
			echo('<tr>');
			echo('<td><pre style="font-family:monospace;">'.$Site['ReferenceDisplay'].'</pre></td>');
			echo('<td>'.htmlspecialchars($Site['ses_name']).'</td>');
			echo('<td>'.$Site['ses_reminderWeekly'].'</td>');
			echo('<td>'.$Site['ses_reminderMonthly'].'</td>');
			if (intval($Site['nb_contacts'])>0) {
				echo('<td>'.$Site['nb_contacts'].' - <a href="page_list_contacts.php?filter_site='.urlencode($Site['path']).'">'._("See list").'</a></td>');
			} else {
				echo('<td class="red">'._("None").'</td>');
			}
			echo('<td><a href="page_list_thresholds.php?filter_site='.urlencode($Site['path']).'">'.$Site['nb_thresholds'].'</a></td>');
			// Report Recipients
			/*if ($Site['ReportRecipients']!='') {
				$Tokens=explode("|",$Site['ReportRecipients']);
				$Recipients="";
				for ($i=0; $i<count($Tokens); $i++) {
					if ($Recipients!="") $Recipients.=", ";
					$Recipients.=GetSiteReferenceFromPath($Tokens[$i]);
				}
				echo('<td>'.htmlspecialchars($Recipients).'</td>');
			} else {
				echo('<td class="disabled">'._("None").'</td>');
			}*/
			// Alert Recipients
            if ($Site['cascading_alert'] == 1) {
                echo('<td class="disabled">'._("AUTO CASCADING ALERTS").'</td>');
            } else if ($Site['AlertRecipients']!='') {
                $Tokens = explode("|", $Site['AlertRecipients']);
                $Recipients = "";
                for ($i = 0; $i < count($Tokens); $i++) {
                    if ($Recipients != "") $Recipients .= ", ";
                    $Recipients .= GetSiteReferenceFromPath($Tokens[$i]);
                }
                echo('<td>'.htmlspecialchars($Recipients).'</td>');
            } else {
                echo('<td class="disabled">'._("None").'</td>');
            }

            echo('<td>'.$Site['ses_alert_preferred_gateway'].'</td>');
			// Threshold Recipients
			/*
			if ($Site['ThresholdRecipients']!='') {
				$Tokens=explode("|",$Site['ThresholdRecipients']);
				$Recipients="";
				for ($i=0; $i<count($Tokens); $i++) {
					if ($Recipients!="") $Recipients.=", ";
					$Recipients.=GetSiteReferenceFromPath($Tokens[$i]);
				}
				echo('<td>'.htmlspecialchars($Recipients).'</td>');
			} else {
				echo('<td class="disabled">'._("None").'</td>');
			}
			*/
			echo('</tr>');
		}
		echo('</table>');
	} else {
		// No sites!
		echo('<p>'._("No site found, please do an import!").'</p>');
	}
	
	// End
	WebFooter();
	db_Close($bdd);
	
?>