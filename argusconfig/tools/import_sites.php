<?php

	require_once("config_functions.php");
	require_once("mysql_functions.php");

    /**
     * Check sites configuration
     *
     * @param $bdd
     * @param $MainNode
     * @param $sites
     * @return array
     */
	function CheckSites($bdd, $MainNode, &$sites)
    {
		// Error list
		$errors_list=array();
		
		// ====================================

		// Step #1 - Reading the sites from XML
		// ====================================
		
		// Get the child nodes
		$site_nodes=xml_find_childs_by_tag($MainNode, "site");

		// Adding Site Root
        $site=array();
        $site['reference'] = 'SitesRoot';
        $site['parent_site_reference'] = null;
        $site['name'] = 'SitesRoot';
        $site['path'] = '/SitesRoot';
        $site['parentPath'] = '';
        $site["weekly_reminder_overrun_minutes"] = 0;
        $site["monthly_reminder_overrun_minutes"] = 0;
        $site['report_recipients'] = array();
        $site['alert_preferred_gateway'] = false;
        $site['cascading_alert'] = 0;
        $site['alert_recipients'] = array();
        $site['threshold_recipients'] = array();
        $sites[$site['reference']] = $site;

        // Adding Xml sites
		for ($i=0; $i<count($site_nodes); $i++) {
			// New site
			$site=array();
			// First level values
			$site['reference']=xml_get_child_value($site_nodes[$i],"reference");
			$site['parent_site_reference']=xml_get_child_value($site_nodes[$i],"parent_site_reference");
			$site['name']=xml_get_child_value($site_nodes[$i],"name");
			$site['weekly_reminder_overrun_minutes']=xml_get_child_value($site_nodes[$i],"weekly_reminder_overrun_minutes");
			$site['monthly_reminder_overrun_minutes']=xml_get_child_value($site_nodes[$i],"monthly_reminder_overrun_minutes");
            //Cascading Alert
            $site['cascading_alert'] = xml_get_child_value($site_nodes[$i],"cascading_alert");

			// Managing optional values
			if ($site['name']===FALSE) {
				$site['name']=$site['reference'];
			}
			if ($site['weekly_reminder_overrun_minutes']===FALSE) {
				$site['weekly_reminder_overrun_minutes']=ConfigGetInteger($bdd, "weekly_reminder_overrun_minutes");
			} else {
				$site['weekly_reminder_overrun_minutes']=intval($site['weekly_reminder_overrun_minutes']);
			}
			if ($site['monthly_reminder_overrun_minutes']===FALSE) {
				$site['monthly_reminder_overrun_minutes']=ConfigGetInteger($bdd, "monthly_reminder_overrun_minutes");
			} else {
				$site['monthly_reminder_overrun_minutes']=intval($site['monthly_reminder_overrun_minutes']);
			}
			//Alert Preferred Gateway
            $site['alert_preferred_gateway'] = xml_get_child_value($site_nodes[$i],"alert_preferred_gateway");

            if ($site['cascading_alert']===FALSE || strtolower($site['cascading_alert'])=="no") {
                $site['cascading_alert']=0;
            } else {
                $site['cascading_alert']=1;
            }
			// Getting report recipients
			$site['report_recipients']=array();
			$recipient_nodes=xml_find_sub_childs_by_tag($site_nodes[$i], "report_recipients", "recipient_site_reference");
			for ($j=0; $j<count($recipient_nodes); $j++) {
				$recipient=trim($recipient_nodes[$j]->nodeValue);
				if (array_search($recipient, $site['report_recipients'], TRUE)===FALSE) {
					$site['report_recipients'][]=$recipient;
				}
			}
			// Getting alert recipients
			$site['alert_recipients']=array();
			$alert_nodes=xml_find_sub_childs_by_tag($site_nodes[$i], "alert_recipients", "recipient_site_reference");
			for ($j=0; $j<count($alert_nodes); $j++) {
				$recipient=trim($alert_nodes[$j]->nodeValue);
				if (array_search($recipient, $site['alert_recipients'], TRUE)===FALSE) {
					$site['alert_recipients'][]=$recipient;
				}
			}
			// Getting Threshold recipients
			$site['threshold_recipients']=array();
			$threshold_nodes=xml_find_sub_childs_by_tag($site_nodes[$i], "threshold_recipients", "recipient_site_reference");
			for ($j=0; $j<count($threshold_nodes); $j++) {
				$recipient=trim($threshold_nodes[$j]->nodeValue);
				if (array_search($recipient, $site['threshold_recipients'], TRUE)===FALSE) {
					$site['threshold_recipients'][]=$recipient;
				}
			}
			// Validation of site values
			$Error=FALSE;
			if ($site['reference']===FALSE || trim($site['reference'])=="") {
				$errors_list[]=_("ERROR ImportSites: a site with an empty reference was found!");
				$Error=TRUE;
			} else {
				if (strpos($site['reference'], "/")!==FALSE) {
					$errors_list[]=sprintf(_("ERROR ImportSites: The site reference [%s] contains the invalid / character!"), $site['reference']);
					$Error=TRUE;
				}
				if ($site['parent_site_reference']===FALSE || trim($site['parent_site_reference'])=="") {
					$errors_list[]=sprintf(_("ERROR ImportSites: The site reference [%s] has an empty parent reference!"), $site['reference']);
					$Error=TRUE;
				}
				if (isset($sites[$site['reference']])) {
					$errors_list[]=sprintf(_("ERROR ImportSites: a site with the reference [%s] is already defined!"), $site['reference']);
					$Error=TRUE;
				}
			}
			// Adding site if no error found
			if ($Error==FALSE) {
				$sites[$site['reference']]=$site;
			}
		}

		// ===========================================
		// Step #2 - Checking the parent relationships
		// ===========================================

		// Checking if we had errors
		if (count($errors_list)==0) {
			// Checking if each site can go up to "SitesRoot" in a maximum of levels
			$MaximumLevels=ConfigGetInteger($bdd, "import_sites_maximum_levels");
			foreach ($sites as $reference=>$site) {
			    if ($reference == 'SitesRoot') {
			        continue;
                }

				$current_parent=$site['parent_site_reference'];
				$complete_path="";
				// Max level +1 for /RootSites
				for ($i=1; $i<$MaximumLevels+1; $i++) {
					$complete_path="/".$current_parent.$complete_path;
					if ($current_parent!="SitesRoot") {
						if (isset($sites[$current_parent])) {
							// We go up to the next parent
							$current_parent=$sites[$current_parent]['parent_site_reference'];
						} else {
							// Parent not found !
							$errors_list[]=sprintf(_("ERROR ImportSites: The parent site [%s] was not found!"), $current_parent);
							break;
						}
					} else {
						// Root found, we stop
						break;
					}
				}
				// Save the paths (site and parent)
				$sites[$reference]['path']=$complete_path."/".$reference;
				$sites[$reference]['parentPath']=$complete_path;
				// Check if we can go to SitesRoot for this site
				if (strpos($sites[$reference]['path'],"/SitesRoot")!==0 || strpos($sites[$reference]['parentPath'],"/SitesRoot")!==0) {
					$errors_list[]=sprintf(_("ERROR ImportSites: The site [%s] can't go up to [SitesRoot]!"), $reference);
				}
			}
		}
		
		// =======================================
		// Step #3 - Checking if recipients exists
		// =======================================

		// Checking if we had errors
		if (count($errors_list)==0) {
			foreach ($sites as $reference=>$site) {
				// Checking alert_recipients
				for ($i=0; $i<count($site['alert_recipients']); $i++) {
					if (!isset($sites[$site['alert_recipients'][$i]])) {
						$errors_list[]=sprintf(_("ERROR ImportSites: The alert recipient site [%s] in site [%s] was not found!"), $site['alert_recipients'][$i], $reference);
					}
				}
				// Checking report_recipients
				for ($i=0; $i<count($site['report_recipients']); $i++) {
					if (!isset($sites[$site['report_recipients'][$i]])) {
						$errors_list[]=sprintf(_("ERROR ImportSites: The report recipient site [%s] in site [%s] was not found!"), $site['report_recipients'][$i], $reference);
					}
				}
				// Checking threshold_recipients
				for ($i=0; $i<count($site['threshold_recipients']); $i++) {
					if (!isset($sites[$site['threshold_recipients'][$i]])) {
						$errors_list[]=sprintf(_("ERROR ImportSites: The threshold recipient site [%s] in site [%s] was not found!"), $site['threshold_recipients'][$i], $reference);
					}
				}
			}
		}

		return($errors_list);
	}

    /**
     * Import sites configuration
     *
     * @param $bdd
     * @param $sites
     */
    function ImportSites($bdd, $sites)
    {
        // Insert sites
        foreach ($sites as $reference=>$site) {
            $Values=array(
                "path"                          => "'".$bdd->escape($site["path"])."'",
                "parentPath"                    => "'".$bdd->escape($site["parentPath"])."'",
                "ses_name"                      => "'".$bdd->escape($site["name"])."'",
                "ses_reminderWeekly"            => $site["weekly_reminder_overrun_minutes"],
                "ses_reminderMonthly"           => $site["monthly_reminder_overrun_minutes"],
                "ses_alert_preferred_gateway"   => ($site["alert_preferred_gateway"] !== false ? "'".$site["alert_preferred_gateway"]."'" : "NULL"),
                "cascading_alert"               => $site["cascading_alert"],
            );
            db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"frontline_group", $Values, false);
        }
        // Insert recipients
        foreach ($sites as $reference=>$site) {
            // Report
            for ($i=0; $i<count($site['report_recipients']); $i++) {
                $Values=array(
                    "path"           => "'".$bdd->escape($site["path"])."'",
                    "pathRecipients" => "'".$bdd->escape($sites[$site['report_recipients'][$i]]['path'])."'",
                    "messageType"    => "'REPORT'",
                );
                db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_recipients", $Values,false);
            }
            // Alert
            for ($i=0; $i<count($site['alert_recipients']); $i++) {
                $Values=array(
                    "path"           => "'".$bdd->escape($site["path"])."'",
                    "pathRecipients" => "'".$bdd->escape($sites[$site['alert_recipients'][$i]]['path'])."'",
                    "messageType"    => "'ALERT'",
                );
                db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_recipients", $Values,false);
            }
            // Threshold
            for ($i=0; $i<count($site['threshold_recipients']); $i++) {
                $Values=array(
                    "path"           => "'".$bdd->escape($site["path"])."'",
                    "pathRecipients" => "'".$bdd->escape($sites[$site['threshold_recipients'][$i]]['path'])."'",
                    "messageType"    => "'THRESHOLD'",
                );
                db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_recipients", $Values,false);
            }
        }
    }

?>