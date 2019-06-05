<?php
	require_once ("./tools/web_template.php");
	require_once("./tools/diagnosis.php");
	WebHeader(_("Argus Gateway diagnosis"));

    $phonePrefix = '';

    if (isset($_GET['phoneCode'])) {
        $phonePrefix = "+".$_GET['phoneCode'];
    }

	$diagnosisArgusGateway = run_diagnosis_argus_gateway($phonePrefix);
	// Summary
	echo('<p>'.sprintf(_("Results: %d tests run with %d errors")."\n",$diagnosisArgusGateway['nb_tests'],$diagnosisArgusGateway['nb_errors']).'</p>');
	// Show diagnosis
	if (count($diagnosisArgusGateway['tests'])>0) {
		echo('<table>');
		echo('<tr><th>'._("Result").'</th><th>'._("Test").'</th><th>'._("Information").'</th><th>'._("Device Id").'</th><th>'._("Operator").'</th><th>'._("Manufacturer").'</th><th>'._("Model").'</th><th>'._("Version").'</th><th>'._("Battery").'</th><th>'._("Power").'</th><th>'._("Pending Messages").'</th><th>'._("Poll Interval").'</th><th>'._("Last Update").'</th></tr>');
		for ($i=0; $i<count($diagnosisArgusGateway['tests']); $i++) {
			echo('<tr>');
			if ($diagnosisArgusGateway['tests'][$i]['OK']==TRUE) {
				echo('<td class="green">'._("OK").'</td>');
			} else {
				echo('<td class="red">'._("Error").'</td>');
			}
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Test']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Information']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Device Id']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Operator']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Manufacturer']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Model']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Version']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Battery']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Power']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Pending Messages']).'</td>');
            echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Poll Interval']).'</td>');
			echo('<td>'.htmlspecialchars($diagnosisArgusGateway['tests'][$i]['Last Update']).'</td>');
			echo('</tr>');
		}
		echo('</table>');
	} else {
		echo('<p>'._("No device found!").'</p>');
	}

	WebFooter();
?>