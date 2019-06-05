<?php

	// Start
	require_once ("./tools/web_template.php");
	require_once ("./tools/string_functions.php");
	$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);

	// Filter by site
	$Where="1=1";
	$FilterLabel="";
	$filter_site=GetStringParameter("filter_site","");
	if ($filter_site!="") {
		if ($Where!="") $Where.=" AND ";
		$Where.=" gm.group_path='".$bdd->escape($filter_site)."' ";
		if ($FilterLabel!="") $FilterLabel.=_(" and ");
		$FilterLabel.=" ".sprintf(_("for site %s"), GetSiteReferenceFromPath($filter_site));
	}
	
	// Sending HTML headers and notification
	WebHeader(_("Contacts list").$FilterLabel);	
	
	// Get contacts
	$SQL="
		SELECT 
			c.active,
			c.emailAddress,
			c.name,
			c.notes,
			c.phoneNumber,
			c.imei,
			c.imei2,
			c.alertPreferredGateway,
			gm.group_path
		FROM 
			contact c 
			INNER JOIN groupmembership gm ON gm.contact_contact_id=c.contact_id
		WHERE 
			".$Where."
		ORDER BY
			c.name ASC
	;";
	$Contacts=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
	if (count($Contacts)>0) {
		echo('<p>'.sprintf(_("%d contacts found:"),count($Contacts)).'</p>');
		// Display the results
		echo('<table>');
		echo('<tr><th>'._("Name").'</th><th>'._("Phone number").'</th><th>'._("IMEI").'</th><th>'._("IMEI2").'</th><th>'._("Alert Preferred Gateway").'</th><th>'._("Active").'</th><th>'._("Site reference").'</th><th>'._("Email").'</th><th>'._("Notes").'</th></tr>');
		for ($i=0; $i<count($Contacts); $i++) {
			echo('<tr>');
			echo('<td>'.htmlspecialchars($Contacts[$i]['name']).'</td>');
			echo('<td>'.htmlspecialchars($Contacts[$i]['phoneNumber']).'</td>');
			echo('<td>'.htmlspecialchars($Contacts[$i]['imei']).'</td>');
			echo('<td>'.htmlspecialchars($Contacts[$i]['imei2']).'</td>');
            echo('<td>'.htmlspecialchars($Contacts[$i]['alertPreferredGateway']).'</td>');
			if (intval($Contacts[$i]['active'])==1) {
				echo('<td class="green">'._("Yes").'</td>');
			} else {
				echo('<td class="red">'._("No").'</td>');
			}
			echo('<td>'.htmlspecialchars(GetSiteReferenceFromPath($Contacts[$i]['group_path'])).'</td>');
			if ($Contacts[$i]['emailAddress']!='') {
				echo('<td>'.htmlspecialchars($Contacts[$i]['emailAddress']).'</td>');
			} else {
				echo('<td class="disabled">'._("None").'</td>');
			}
			if ($Contacts[$i]['notes']!='') {
				echo('<td>'.htmlspecialchars($Contacts[$i]['notes']).'</td>');
			} else {
				echo('<td class="disabled">'._("None").'</td>');
			}
			echo('</tr>');
		}
		echo('</table>');
	} else {
		// No contacts!
		echo('<p>'._("No contact found, please do an import!").'</p>');
	}
	
	// End
	WebFooter();
	db_Close($bdd);

?>