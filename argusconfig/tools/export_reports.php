<?php

	require_once("string_functions.php");
	require_once("mysql_functions.php");
	require_once("epidemiology.php");
	require_once ("Utils/Constant.php");

	// Export the not yet exported reports in a XML file
	function ExportReports () {
		global $config;
		$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
		// Getting reports
		$nb_waiting=db_RowsCount($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_datavalues","exported IS NULL");
		if ($nb_waiting>0) {
			// Export needed
            $dateFile = date("Ymd-His");
			$Filename = $config["path_data_ouput"].$dateFile.'-reports.tmp';
			$finalFileName = $config["path_data_ouput"].$dateFile.'-reports.xml';
			$Message=sprintf(_("Exporting %d reports values or alerts values in XML file [%s]"),$nb_waiting, $finalFileName);
			echo($Message."\n");
			LogMessage("tasks", $Message);
			// Create the output file with header (XML, UTF-8 with BOM)
			$f=fopen($Filename, "w");
			fwrite($f, chr(0xEF).chr(0xBB).chr(0xBF).'<?xml version="1.0" encoding="utf-8"?>'."\r\n");
			fwrite($f, '<export>'."\r\n");
			// ---------------
			// Get the reports
			// ---------------
			$reports=getReports($bdd);

			if (count($reports)>0) {
				fwrite($f, "\t".'<reports>'."\r\n");
				for ($i=0; $i<count($reports); $i++) {
					// Write report
					fwrite($f, "\t\t".'<report>'."\r\n");
					fwrite($f, "\t\t\t".'<contact><![CDATA['.$reports[$i]['contactName'].']]></contact>'."\r\n");
					fwrite($f, "\t\t\t".'<phone_number><![CDATA['.$reports[$i]['contactPhoneNumber'].']]></phone_number>'."\r\n");
					fwrite($f, "\t\t\t".'<site><![CDATA['.GetSiteReferenceFromPath($reports[$i]['path']).']]></site>'."\r\n");
					$ts_reception=strtotime($reports[$i]['reception']);
					fwrite($f, "\t\t\t".'<reception_date>'.date("Y-m-d",$ts_reception).'T'.date("H:i:s",$ts_reception).'</reception_date>'."\r\n");
					fwrite($f, "\t\t\t".'<disease><![CDATA['.$reports[$i]['disease'].']]></disease>'."\r\n");
					fwrite($f, "\t\t\t".'<period><![CDATA['.$reports[$i]['period'].']]></period>'."\r\n");
					$ts_start=strtotime($reports[$i]['periodStart']);
					fwrite($f, "\t\t\t".'<start_date>'.date("Y-m-d",$ts_start).'</start_date>'."\r\n");
					if ($reports[$i]['period'] == Constant::PERIOD_WEEKLY ){
						$epi = Timestamp2Epi($ts_start) ;
						if (isset($epi)) {
							fwrite($f, "\t\t\t" . '<week>' . $epi['Week'] . '</week>' . "\r\n");
							fwrite($f, "\t\t\t" . '<year>' . $epi['Year'] . '</year>' . "\r\n");
						}
						else{
							// TODO Errors
						}
					}
					elseif ($reports[$i]['period'] == Constant::PERIOD_MONTHLY ){
						fwrite($f, "\t\t\t".'<month>'.date("m",$ts_start).'</month>'."\r\n");
						fwrite($f, "\t\t\t" . '<year>'.date("Y",$ts_start).'</year>' . "\r\n");
					}

					if ($reports[$i]['reportId'] != null) {
						fwrite($f, "\t\t\t".'<report_id><![CDATA['.$reports[$i]['reportId'].']]></report_id>'."\r\n");
					}

					fwrite($f, "\t\t\t".'<values>'."\r\n");
					$keys = explode('|', $reports[$i]['dataKeys']);
					$values = explode('|', $reports[$i]['dataValues']);

					for ($j=0; $j<count($keys); $j++) {
						if (isset($keys[$j]) && isset($values[$j])) {
							fwrite($f, "\t\t\t\t".'<value>'."\r\n");
							fwrite($f, "\t\t\t\t\t".'<value_reference><![CDATA['.$keys[$j].']]></value_reference>'."\r\n");
							fwrite($f, "\t\t\t\t\t".'<data><![CDATA['.$values[$j].']]></data>'."\r\n");
							fwrite($f, "\t\t\t\t".'</value>'."\r\n");
						}
					}
					fwrite($f, "\t\t\t".'</values>'."\r\n");
					fwrite($f, "\t\t".'</report>'."\r\n");
					// Set report as exported
                    setReportExported($bdd, $reports[$i]['FK_dataId']);
				}
				fwrite($f, "\t".'</reports>'."\r\n");
				$Message=sprintf(_("%d reports exported"),count($reports));
				echo($Message."\n");
				LogMessage("tasks", $Message);
			}
			// --------------
			// Get the alerts
			// --------------
			$reports=getAlerts($bdd);
			if (count($reports)>0) {
				fwrite($f, "\t".'<alerts>'."\r\n");
				for ($i=0; $i<count($reports); $i++) {
				    // TODO : Check if we get all values from an ALERT : Ajouter RID dans l'alerte

					// Write report
					fwrite($f, "\t\t".'<alert>'."\r\n");
					fwrite($f, "\t\t\t".'<contact><![CDATA['.$reports[$i]['contactName'].']]></contact>'."\r\n");
					fwrite($f, "\t\t\t".'<phone_number><![CDATA['.$reports[$i]['contactPhoneNumber'].']]></phone_number>'."\r\n");
					fwrite($f, "\t\t\t".'<site><![CDATA['.GetSiteReferenceFromPath($reports[$i]['path']).']]></site>'."\r\n");
					$ts_reception=strtotime($reports[$i]['reception']);
					fwrite($f, "\t\t\t".'<reception_date>'.date("Y-m-d",$ts_reception).'T'.date("H:i:s",$ts_reception).'</reception_date>'."\r\n");
                    $keys = explode('|', $reports[$i]['dataKeys']);
                    $values = explode('|', $reports[$i]['dataValues']);
					$message="";
                    for ($j=0; $j<count($keys); $j++) {
                        if (isset($keys[$j]) && isset($values[$j])) {
                            if ($message!="") {
                                $message.=", ";
                            }
                            $message.=$keys[$j]."=".$values[$j];
                        }
                    }
					fwrite($f, "\t\t\t".'<message><![CDATA['.$message.']]></message>'."\r\n");
					fwrite($f, "\t\t".'</alert>'."\r\n");
					// Set report as exported
                    setReportExported($bdd, $reports[$i]['FK_dataId']);
				}
				fwrite($f, "\t".'</alerts>'."\r\n");
				$Message=sprintf(_("%d alerts exported"),count($reports));
				echo($Message."\n");
				LogMessage("tasks", $Message);
			}
			// End of XML file
			fwrite($f, '</export>'."\r\n");
			fclose($f);

			// Give the xml extension to the file.
            rename($Filename, $finalFileName);

            // At this point the file can be processed so we can call setReportExported for all exported reports & alerts.

			$Message=_("End of export");
			echo($Message."\n");
			LogMessage("tasks", $Message);
		} else {
			// Nothing to do
			echo(_("No report to export")."\n");
		}
		db_Close($bdd);
	}

    /**
     * Return reports and values to export
     *
     * @param $bdd
     * @return array
     */
	function getReports($bdd)
    {
        return getDataValues($bdd, ['Weekly','Monthly']);
    }

    /**
     * Return alerts and values to export
     *
     * @param $bdd
     * @return array
     */
    function getAlerts($bdd)
    {
        return getDataValues($bdd, ['None']);
    }

    /**
     * Return values & reports / alerts
     *
     * @param $bdd
     * @param $period
     * @return array
     */
    function getDataValues($bdd, $period)
    {
		$tableFields = 'd.id, d.path, d.reception, d.exported, d.contactName, d.contactPhoneNumber, d.disease, d.period, d.periodStart, d.reportId, v.FK_dataId';
        $SQL="SELECT " . $tableFields . ", group_concat(v.key SEPARATOR '|') AS dataKeys, group_concat(v.value SEPARATOR '|') AS dataValues
              FROM ses_data d INNER JOIN ses_datavalues v ON d.id = v.FK_dataId
              WHERE v.exported IS NULL AND d.period IN ('" . implode( "', '", $period ) . "')
              GROUP BY d.id
              ORDER BY reception ASC;";
        return db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
    }

    /**
     * Update report and values as exported now.
     *
     * @param $bdd
     * @param $reportId
     */
    function setReportExported($bdd, $reportId)
    {
        $SQL="UPDATE ses_data d INNER JOIN ses_datavalues v ON d.id = v.FK_dataId SET d.exported=NOW(), v.exported=NOW() WHERE d.id=".$reportId;
        db_Query ($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
    }

?>
