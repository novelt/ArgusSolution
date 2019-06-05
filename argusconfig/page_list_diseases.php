<?php

	// Start
	require_once ("./tools/web_template.php");
	require_once ("./tools/string_functions.php");
	$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);

	// display values for a specified period
	function display_values($bdd,$Disease,$Period,$Title) {
		$SQL="SELECT * FROM ses_disease_values WHERE period='".$bdd->escape($Period)."' AND disease='".$bdd->escape($Disease)."' ORDER BY position ASC;";
		$Values=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		if (count($Values)>0) {
			echo('<p style="font-weight:normal;text-decoration:underline;">'.htmlspecialchars($Title).'</p>');
			echo('<table>');
			echo('<tr><th>'._("Position").'</th><th>'._("Value").'</th><th>'._("Keywords").'</th><th>'._("Type").'</th><th>'._("Mandatory").'</th></tr>');
			for ($j=0; $j<count($Values); $j++) {
				echo('<tr>');
				echo('<td>'.$Values[$j]['position'].'</td>');
				echo('<td>'.htmlspecialchars($Values[$j]['value']).'</td>');
				echo('<td>'.htmlspecialchars($Values[$j]['keywords']).'</td>');
				echo('<td>'.$Values[$j]['datatype'].'</td>');
				if (intval($Values[$j]['mandatory'])==1) {
					echo('<td class="green">'._("Yes").'</td>');
				} else {
					echo('<td class="red">'._("No").'</td>');
				}
				echo('</tr>');
			}
			echo('</table>');
		}
	}

	function display_constraints($bdd,$Disease,$Title){
		$SQL="SELECT * FROM ses_disease_constraints WHERE disease='".$bdd->escape($Disease)."' ORDER BY id ASC;";
		$Values=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		if (count($Values)>0) {
			echo('<p style="font-weight:normal;text-decoration:underline;">'.htmlspecialchars($Title).'</p>');
			echo('<table>');
			echo('<tr><th>'._("Value").'</th><th>'._("Operator").'</th><th>'._("Value").'</th><th>'._("Period").'</th></tr>');
			for ($j=0; $j<count($Values); $j++) {
				echo('<tr>');
				echo('<td>'.htmlspecialchars($Values[$j]['value_from']).'</td>');
				echo('<td>'.htmlspecialchars($Values[$j]['operator']).'</td>');
				echo('<td>'.htmlspecialchars($Values[$j]['value_to']).'</td>');
				echo('<td>'.htmlspecialchars($Values[$j]['period']).'</td>');
				echo('</tr>');
			}
			echo('</table>');
		}

	}
	
	// Sending HTML headers and notification
	WebHeader(_("Alert and diseases list"));	
	
	// Get diseases
	$SQL="
		SELECT
			(CASE WHEN disease='".$bdd->escape($config["alert_reference"])."' THEN 1 ELSE 0 END) As 'Alert',
			disease,
			name,
			keywords
		FROM 
			ses_diseases
		ORDER BY
			Alert DESC,
			disease ASC
	;";
	$Diseases=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
	if (count($Diseases)>0) {
		echo('<p>'.sprintf(_("%d definitions found:"),count($Diseases)).'</p>');
		// Display the results
		for ($i=0; $i<count($Diseases); $i++) {
			// Check if it's an alert for the title
			$Alert=intval($Diseases[$i]['Alert']);
			if ($Alert==1) {
				echo('<div class="title1">'._("Alert message").'</div>');
			} else {
				echo('<div class="title1">'.htmlspecialchars($Diseases[$i]['name']).'</div>');
			}
			// Disease informations
            if ($Alert==0) {
			echo('<table>');
			echo('<tr><th>'._("Reference").'</th><td>'.$Diseases[$i]['disease'].'</td></tr>');
			echo('<tr><th>'._("Keywords").'</th><td>'.$Diseases[$i]['keywords'].'</td></tr>');
			echo('</table>');
            }
			// Weekly values
			display_values($bdd,$Diseases[$i]['disease'],'Weekly',_("Weeky values:"));
			// Monthly values
			display_values($bdd,$Diseases[$i]['disease'],'Monthly',_("Monthly values:"));
			// Alert values
			display_values($bdd,$Diseases[$i]['disease'],'None',_("Alert values:"));

			// Constraints
			display_constraints($bdd,$Diseases[$i]['disease'],_("Constraints:"));
		}
	} else {
		// No diseases!
		echo('<p>'._("No disease or alert found, please do an import!").'</p>');
	}
	
	// End
	WebFooter();
	db_Close($bdd);
	
?>