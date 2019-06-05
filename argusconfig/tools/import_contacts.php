<?php

	require_once("config_functions.php");
	require_once("mysql_functions.php");
	require_once("string_functions.php");

    /**
     * Check contacts configuration
     *
     * @param $bdd
     * @param $MainNode
     * @param $contacts
     * @param $sites
     * @return array
     */
	function CheckContacts($bdd, $MainNode, &$contacts, $sites)
    {
		// Error list
		$errors_list=array();
		
		// =======================================
		// Step #1 - Reading the contacts from XML
		// =======================================
		
		// Get the child nodes
		$contact_nodes=xml_find_childs_by_tag($MainNode, "contact");

		for ($i=0; $i<count($contact_nodes); $i++) {
			// New contact
			$contact=array();
			// First level values
			$contact['phone_number']=CleanPhoneNumber(xml_get_child_value($contact_nodes[$i],"phone_number"));
			$contact['imei']=xml_get_child_value($contact_nodes[$i],"imei");
			$contact['imei2']=xml_get_child_value($contact_nodes[$i],"imei2");
			$contact['site_reference']=xml_get_child_value($contact_nodes[$i],"site_reference");
			$contact['name']=xml_get_child_value($contact_nodes[$i],"name");
			$contact['enabled']=xml_get_child_value($contact_nodes[$i],"enabled");
			$contact['email']=xml_get_child_value($contact_nodes[$i],"email");
			$contact['note']=xml_get_child_value($contact_nodes[$i],"note");
            $contact['alert_preferred_gateway']=xml_get_child_value($contact_nodes[$i],"alert_preferred_gateway");
			// Managing optional values
			if ($contact['enabled']===FALSE || strtolower($contact['enabled'])=="yes") {
				$contact['enabled']=1;
			} else {
				$contact['enabled']=0;
			}
			if ($contact['email']===FALSE) {
				$contact['email']='';
			}
			if ($contact['note']===FALSE) {
				$contact['note']='';
			}
			// Validation of contact values
			$Error=FALSE;
			if ($Error==FALSE && CheckPhoneNumber($contact['phone_number'])==FALSE) {
				// Wrong phone number format
				$errors_list[]=sprintf(_("ERROR ImportContacts: The phone number [%s] for contact [%s] isn't correclty formatted (+XXXX expected)"),$contact['phone_number'],$contact['name']);
				$Error=TRUE;
			}
			if ($Error==FALSE && isset($contacts[$contact['phone_number']])) {
				// Phone number already used
				$errors_list[]=sprintf(_("ERROR ImportContacts: The phone number [%s] for contact [%s] is already declared (must be unique)"),$contact['phone_number'],$contact['name']);
				$Error=TRUE;	
			}

            if ($Error==FALSE && $contact['site_reference'] != 'SitesRoot' && array_key_exists ($contact['site_reference'], $sites)==FALSE) {
                // Site doesn't exist
                $errors_list[]=sprintf(_("ERROR ImportContacts: The site reference [%s] for contact [%s] isn't existing"),$contact['site_reference'],$contact['name']);
                $Error=TRUE;
            }

			// Adding site if no error found
			if ($Error==FALSE) {
				$contacts[$contact['phone_number']]=$contact;
			}
		}

		return($errors_list);
	}

    /**
     * Import contacts configuration
     *
     * @param $bdd
     * @param $contacts
     * @param $sites
     */
    function ImportContacts($bdd, $contacts, $sites)
    {
        // Insert contacts
        foreach ($contacts as $phone_number => $contact) {
            $Values = array(
                "active" => $contact['enabled'],
                "emailAddress" => "'" . $bdd->escape($contact["email"]) . "'",
                "name" => "'" . $bdd->escape($contact["name"]) . "'",
                "notes" => "'" . $bdd->escape($contact["note"]) . "'",
                "otherPhoneNumber" => "''",
                "phoneNumber" => "'" . $bdd->escape($contact["phone_number"]) . "'",
                "imei" => "'" . $bdd->escape($contact["imei"]) . "'",
                "imei2" => "'" . $bdd->escape($contact["imei2"]) . "'",
                "alertPreferredGateway" =>  ($contact["alert_preferred_gateway"] !== false ? "'" . $bdd->escape($contact["alert_preferred_gateway"]) . "'" : "NULL"),
            );
            db_Insert($bdd, __FUNCTION__, __LINE__, __FILE__, "contact", $Values, false);
            // Get the contact_id
            $contact_id = db_LastInsertedId($bdd, __FUNCTION__, __LINE__, __FILE__);

            // Create relationship with site
            $site = $sites[$contact['site_reference']];

            if (isset($site) && isset($site['path'])) {
                $Values = array(
                    "contact_contact_id" => $contact_id,
                    "group_path" => "'" . $bdd->escape($site['path']) . "'",
                );
                db_Insert($bdd, __FUNCTION__, __LINE__, __FILE__, "groupmembership", $Values,false);
            }
        }
    }

?>