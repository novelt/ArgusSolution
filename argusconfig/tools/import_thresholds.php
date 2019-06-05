<?php

	require_once("config_functions.php");
	require_once("mysql_functions.php");
	require_once("epidemiology.php");
	require_once("string_functions.php");


    /**
     * Check thresholds configuration
     *
     * @param $bdd
     * @param $MainNode
     * @param $thresholds
     * @param $sites
     * @param $diseases
     * @return array
     */
	function CheckThresholds($bdd, $MainNode, &$thresholds, $sites, $diseases)
    {
		global $config;

		// Error list
		$errors_list=array();
		
		// =========================================
		// Step #1 - Reading the thresholds from XML
		// =========================================
		
		// Get the child nodes
		$threshold_nodes=xml_find_childs_by_tag($MainNode, "threshold");

		for ($i=0; $i<count($threshold_nodes); $i++) {
			// New threshold
			$threshold=array();
			// First level values
			$threshold['site_reference']=xml_get_child_value($threshold_nodes[$i],"site_reference");
			$threshold['disease_reference']=xml_get_child_value($threshold_nodes[$i],"disease_reference");
            $threshold['value_reference']=xml_get_child_value($threshold_nodes[$i],"value_reference");
			$threshold['period']=xml_get_child_value($threshold_nodes[$i],"period");
			$threshold['weekNumber']=xml_get_child_value($threshold_nodes[$i],"week_number");
			$threshold['monthNumber']=xml_get_child_value($threshold_nodes[$i],"month_number");
			$threshold['year']=xml_get_child_value($threshold_nodes[$i],"year");
			$threshold['max_value']=intval(xml_get_child_value($threshold_nodes[$i],"max_value"));

			// No error
			$Error=FALSE;

			// Checking Year
			if ($Error==FALSE) {
				if (! isset($threshold['year'])
					|| !is_numeric($threshold['year'])
					|| ! (strlen($threshold['year']) == 4) ) {

					// Error, not a valid period
					$errors_list[]=sprintf(_("ERROR ImportThresholds: An invalid year has been found for site %s, disease %s and Period %s!"), $threshold['site_reference'],$threshold['disease_reference'], $threshold['period'] );
					$Error=TRUE;
				}
			}

			// Checking Period, WeekNumber and MonthNumber
			if ($Error==FALSE) {
				if ($threshold['period']=='Weekly') {
					if (isset($threshold['monthNumber']) && $threshold['monthNumber']!=FALSE ){
						// Error, monthNumber found for Period Weekly
						$errors_list[]=sprintf(_("ERROR ImportThresholds: A monthNumber has been found for site %s, disease %s, year %s and Period %s!"), $threshold['site_reference'],$threshold['disease_reference'],$threshold['year'], $threshold['period'] );
						$Error=TRUE;
					}

					if ( (isset($threshold['weekNumber'])&& $threshold['weekNumber']!=FALSE)
						&& (!is_numeric($threshold['weekNumber'])
							|| !(intval($threshold['weekNumber']) >= 1)
							|| !(intval($threshold['weekNumber']) <= 53) )) {

						// Error, not a valid weekNumber
						$errors_list[]=sprintf(_("ERROR ImportThresholds: An invalid weekNumber has been found for site %s, disease %s and Period %s!"), $threshold['site_reference'],$threshold['disease_reference'], $threshold['period'] );
						$Error=TRUE;
					}
				} elseif ($threshold['period']=='Monthly') {
					if (isset($threshold['weekNumber']) && $threshold['weekNumber']!=FALSE ){
						// Error, weekNumber found for Period Monthly
						$errors_list[]=sprintf(_("ERROR ImportThresholds: A weekNumber has been found for site %s, disease %s, year %s and Period %s!"), $threshold['site_reference'],$threshold['disease_reference'],$threshold['year'], $threshold['period'] );
						$Error=TRUE;
					}

					if ( (isset($threshold['monthNumber']) && $threshold['monthNumber']!=FALSE)
						&& (!is_numeric($threshold['monthNumber'])
							|| !(intval($threshold['monthNumber']) >= 1)
							|| !(intval($threshold['monthNumber']) <= 12) )) {

						// Error, not a valid monthNumber
						$errors_list[]=sprintf(_("ERROR ImportThresholds: An invalid monthNumber has been found for site %s, disease %s and Period %s!"), $threshold['site_reference'],$threshold['disease_reference'], $threshold['period']);
						$Error=TRUE;
					}
				} else {
					// Error, not a valid period
					$errors_list[]=sprintf(_("ERROR ImportThresholds: An invalid period [%s] has been found for site %s and disease %s!"), $threshold['period'],$threshold['site_reference'],$threshold['disease_reference']);
					$Error=TRUE;
				}
			}

			// Creating the key and a label for the threshold
			$key=$threshold['site_reference'].'|'.$threshold['disease_reference'].'|'.$threshold['value_reference'].'|'.$threshold['period'].'|'.$threshold['weekNumber'].'|'.$threshold['monthNumber'].'|'.$threshold['year'];
			$label=sprintf(_("Site=%s, Disease=%s, Period=%s, WeekNumber=%s, MonthNumber=%s, Year=%s"),$threshold['site_reference'],$threshold['disease_reference'],$threshold['period'],$threshold['weekNumber'],$threshold['monthNumber'],$threshold['year']);
			// Checking site validity
			if ($Error==FALSE) {
				if ($threshold['site_reference']=='') {
					// Wrong reference
					$errors_list[]=_("ERROR ImportThresholds: The site reference can't be empty!")." - ".$label;
					$Error=TRUE;
				} else {
					// Check if site exists
                    if (! isset ($sites[$threshold['site_reference']])) {
						$errors_list[]=_("ERROR ImportThresholds: The site reference isn't defined!")." - ".$label;
						$Error=TRUE;
					}
				}
			}
			// Checking disease validity
			if ($Error==FALSE) {
				if ($threshold['disease_reference']=='' || $threshold['disease_reference']==$config["alert_reference"]) {	
					// Wrong reference
					$errors_list[]=_("ERROR ImportThresholds: The disease reference can't be empty or an alert reference!")." - ".$label;
					$Error=TRUE;
				} else {
					// Check if disease exists
                    if (! isset($diseases[$threshold['disease_reference']])) {
						$errors_list[]=_("ERROR ImportThresholds: The disease reference isn't defined!")." - ".$label;
						$Error=TRUE;
					}
				}
			}
			// Checking values
			if ($Error==FALSE) {
			    if (! isset($threshold['value_reference']) || $threshold['value_reference']=='') {
                    // No value
                    $errors_list[]=_("ERROR ImportThresholds: No value found for the threshold!")." - ".$label;
                    $Error=TRUE;
                } else {
                    $found = false ;

                    if (isset($diseases[$threshold['disease_reference']])) {
                        $disease = $diseases[$threshold['disease_reference']] ;
                        $diseaseKey = $threshold['value_reference']."-".$threshold['period'];

                        if (isset($disease['values'][$diseaseKey])) {
                            $value = $disease['values'][$diseaseKey];

                            if ($value['type'] == 'Integer') {
                                $found = true ;
                            }
                        }
                    }
                    if (!$found) {
                        // Unknown value
                        $errors_list[]=sprintf(_("ERROR ImportThresholds: The value [%s] isn't defined for the disease or it isn't an integer!"),$threshold['value_reference'])." - ".$label;
                        $Error=TRUE;
                    }
                }
			}
			// Checking if threshold exists
			if ($Error==FALSE && isset($thresholds[$key])) {
				$errors_list[]=_("ERROR ImportThresholds: A threshold for these site/disease/period/Week or Month/Year has been already defined!")." - ".$label;
				$Error=TRUE;
			}
			// Adding threshold if no error found
			if ($Error==FALSE) {
				$thresholds[$key]=$threshold;
			}
		}

		return($errors_list);
	}

    /**
     * Import thresholds configuration
     *
     * @param $bdd
     * @param $thresholds
     * @param $sites
     */
    function ImportThresholds($bdd, $thresholds, $sites)
    {
        foreach ($thresholds as $key => $threshold) {
            // Create relationship with site
            $site = $sites[$threshold['site_reference']];

            if (isset($site) && isset($site['path'])) {

                $Values = array(
                    "path" => "'" . $bdd->escape($site['path']) . "'",
                    "disease" => "'" . $bdd->escape($threshold["disease_reference"]) . "'",
                    "period" => "'" . $bdd->escape($threshold["period"]) . "'",
                    "weekNumber" => $threshold["weekNumber"] !== FALSE ? $threshold["weekNumber"] : 'NULL',
                    "monthNumber" => $threshold["monthNumber"] !== FALSE ? $threshold["monthNumber"] : 'NULL',
                    "year" => $threshold["year"],
                    "`maxValue`" => $threshold["max_value"],
                    "`values`" => "'" . $bdd->escape($threshold["value_reference"]) . "'",
                );
                db_Insert($bdd, __FUNCTION__, __LINE__, __FILE__, "ses_thresholds", $Values,false);
            }
        }
    }

?>