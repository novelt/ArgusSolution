<?php

	require_once ("string_functions.php");
	require_once ("AndroidVersionning/action_handlingV1.0.php");
    require_once ("Utils/IncomingSmsConstant.php");

	// Check contact validity and get details
	function GetContactDetails ($bdd, $PhoneNumber) {
		$Results=array(
			"PhoneNumber"       => $PhoneNumber,
			"Valid"             => FALSE,
			"Name"              => "",
			"SitePath"          => "",
            "SiteParentPath"    => "",
			"SiteReference"     => "",
            "CascadingAlert"    => FALSE,
		);
		$SQL="
			SELECT
				c.contact_id,
				c.name,
				c.phoneNumber,
				gm.group_path,
				fg.parentPath,
				fg.ses_name,
				fg.cascading_alert,
				COALESCE(ci.android_version, 0.0) AS android_version
			FROM
				contact c
				INNER JOIN groupmembership gm ON c.contact_id=gm.contact_contact_id
				INNER JOIN frontline_group fg ON gm.group_path = fg.path
				LEFT JOIN ses_contact_information ci ON c.phoneNumber = ci.contact_phoneNumber
			WHERE
				c.active=1
				AND
				c.phoneNumber='".$bdd->escape($PhoneNumber)."'
		;";
		$Contact=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		if (count($Contact)==1) {
			$Results['Valid']           = TRUE;
			$Results['ContactId']	    = $Contact[0]['contact_id'];
			$Results['Name']            = $Contact[0]['name'];
			$Results['SitePath']        = $Contact[0]['group_path'];
            $Results['SiteParentPath']  = $Contact[0]['parentPath'];
			$Results['SiteName']	    = $Contact[0]['ses_name'];
			$Results['SiteReference']   = GetSiteReferenceFromPath($Contact[0]['group_path']);
			$Results['PhoneNumber']     = $Contact[0]['phoneNumber'];
            $Results['CascadingAlert']  = ($Contact[0]['cascading_alert'] == 1 ? TRUE : FALSE);

			// Get the version of the Android Application
			$Results['AndroidVersion'] = $Contact[0]['android_version'] ;
		}

		return($Results);
	}

    /**
     * Get a tokenized version of a message
     * Message format : MainKeyword Key1=Value1,Key2=Value2,etc.
     * @param $OriginalMessage
     * @param $Timestamp
     * @return array
     */
	function TokenizeReceivedMessage($OriginalMessage, $Timestamp) {
		// Cleaning carriage returns
		$Message=str_replace(array(chr(10),chr(13))," ",$OriginalMessage);
		// Getting the main keyword, the first one
		$MainKeyword='';
		$first_space=strpos($Message," ");
		if ($first_space!==FALSE && $first_space>0) {
			// Get the main keyword
			// $MainKeyword=trim(strtoupper(substr($Message,0,$first_space)));
			$MainKeyword=CreateKeyword(substr($Message,0,$first_space));
			// Remove the main keyword from the message
			$Message=trim(substr($Message,$first_space));
		} else {
			// No space, the message is the main keyword
			// $MainKeyword=trim(strtoupper($Message));
			$MainKeyword=CreateKeyword($Message);
			// No more message content
			$Message="";
		}
		// Getting the key=value pairs
		$ParsingError=FALSE;
		$Collection=array();
		if ($Message!="") {
			// Gettings pairs separated by ","
			$Pairs=explode(',',$Message);
			for ($i=0; $i<count($Pairs); $i++) {
				$Tokens=explode('=',$Pairs[$i]);
				if (count($Tokens)==2) {
					// We have a pair
					// $Collection[strtoupper(trim($Tokens[0]))]=trim($Tokens[1]);
					$Collection[CreateKeyword($Tokens[0])]=trim($Tokens[1]);
				} else {
					// Error, we are exepecting exactly one "=" per pair
					$ParsingError=TRUE;
				}
			}
		}
		// Returning the results
		$Results=array();
		$Results['MainKeyword']=$MainKeyword;
		$Results['Collection']=$Collection;
		$Results['ParsingError']=$ParsingError;
		$Results['Reception']=$Timestamp;
		$Results['Message']=$OriginalMessage;
		return($Results);
	}

	// Check if global keyword if found and if action is enabled (if KeyEnable is not empty)
	function CheckMainKeyword($bdd, $MainKeyword, $KeyEnable, $KeyKeywords) {
		$ActionValid=FALSE;
		// Check if action enabled if asked
		if ($KeyEnable=='' || ConfigGetInteger ($bdd, $KeyEnable)==1) {
			// Check if the keyword is for this action
			$Keywords=explode(",",ConfigGetString ($bdd, $KeyKeywords));
			for ($i=0; $i<count($Keywords); $i++) {
				if ($MainKeyword==strtoupper(trim($Keywords[$i]))) {
					// Keyword found!
					$ActionValid=TRUE;
				}
			}
		}
		return($ActionValid);
	}

	// Check if global keyword "-TEST" if found and if action is enabled (if KeyEnable is not empty)
	//TODO : To refactor with CheckMainKeyword function
	function CheckMainKeywordTestMode($bdd, $MainKeyword, $KeyEnable, $KeyKeywords) {
		$ActionValid=FALSE;
		// Check if action enabled if asked
		if ($KeyEnable=='' || ConfigGetInteger ($bdd, $KeyEnable)==1) {
			// Check if the keyword is for this action
			$Keywords=explode(",",ConfigGetString ($bdd, $KeyKeywords)."TEST" );
			for ($i=0; $i<count($Keywords); $i++) {
				if ($MainKeyword==strtoupper(trim($Keywords[$i]))) {
					// Keyword found!
					$ActionValid=TRUE;
				}
			}
		}
		return($ActionValid);
	}

	// Get the main keyword for a global keyword
	function GetMainKeyword($bdd, $KeyKeywords) {
		return(GetPrimaryKeyword(ConfigGetString ($bdd, $KeyKeywords)));
	}

	//Check android version and update the referrer contact information
	function UpdateContactAndroidVersion($bdd, $ContactDetails, $TokenizedMessage)
	{
		$Version = "0.0" ;

		foreach ($TokenizedMessage['Collection'] as $Key=>$Value) {
			// Version
			if ($Key == "VERSION") {
				$Version=$Value;
				$RemoveKeys[]=$Key;
				break ;
			}
		}
		// Remove already processed keys - Keep only values
		if (isset($RemoveKeys)) {
			for ($i = 0; $i < count($RemoveKeys); $i++) {
				unset($TokenizedMessage['Collection'][$RemoveKeys[$i]]);
			}
		}

		// Update or insert this information into the database

		$SQL=" INSERT INTO ses_contact_information
 			   VALUES ('".$ContactDetails['PhoneNumber']."', '".$Version."')
 			   ON DUPLICATE KEY UPDATE android_version='".$Version."' ;";

		 db_Query($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

		return $Version;
	}

    /**
     * Check if message is a config SMS or a config SMS Ack
     *
     * @param $message
     *
     * @return bool
     */
	function isConfigurationSms($message)
    {
        global $config;

        if (!isset($config["config_SMS"]) || !is_array($config["config_SMS"])) {
            return false ;
        }

        foreach ($config["config_SMS"] as $configKeyWord){
            if (substr($message, 0, strlen($configKeyWord)) == $configKeyWord){
                return true;
            }
        }

        return false ;
    }

	// Decrypt message
	function decrypt_message($sender_name, $sender_number, $message_content, $serverId, $logFileName)
    {
        global $config;
        global $message_identifier_value, $message_identifier_receiver, $report_identifier_id;

        // Start
        $bdd = db_Open(__FUNCTION__, __LINE__, __FILE__);
        $smsStatus = IncomingSmsStatus::STATUS_NEW;
        $smsType = IncomingSmsType::TYPE_UNKNOWN;

        // Log all received messages and display them for debugging
        $Message = sprintf(_("=> SMS received from %s (%s), message=[%s]"), $sender_number, $sender_name, $message_content);
        //echo($Message."\n");
        LogMessage($logFileName, $Message);

        // Information about processing
        $information = _("Nothing done");

        //Result Value about processing
        $resultValue = TRUE;

        if (is_a_gatewayNumber($bdd, $sender_number) > 0) {
            $smsStatus = IncomingSmsStatus::STATUS_PHONE_NUMBER_GATEWAY;
            $information = _("The phone number correspond to a gateway number. Stop here to avoid loop");

        } else {
            // Check contact validity
            $ContactDetails = GetContactDetails($bdd, $sender_number);
            if ($ContactDetails['Valid'] == TRUE) {
                // Analyze
                $TokenizedMessage = TokenizeReceivedMessage($message_content, time());
                $ProcessingDone = FALSE;

                if ($TokenizedMessage['ParsingError'] == FALSE) {
                    $smsType = IncomingSmsType::TYPE_ARGUS;
                    // Checking and handling the optional prefix message identifier
                    // The message identifier will be sent back to the sender in the SendSMS function
                    $android_keyword_id = $config["android_keyword_id"];

                    if (isset($TokenizedMessage["Collection"][$android_keyword_id])) {
                        // Message identifier present
                        $message_identifier_value = $android_keyword_id . "=" . $TokenizedMessage["Collection"][$android_keyword_id] . " ";
                        $message_identifier_receiver = $sender_number;
                        // Removing the extra token
                        unset($TokenizedMessage["Collection"][$android_keyword_id]);
                    }

                    // Checking the REPORTID keyWord
                    $report_keyword_id = $config["report_keyword_id"];

                    if (isset($TokenizedMessage["Collection"][$report_keyword_id])) {
                        // Message identifier present
                        $report_identifier_id = $TokenizedMessage["Collection"][$report_keyword_id];
                        // Removing the extra token
                        unset($TokenizedMessage["Collection"][$report_keyword_id]);
                    }

                    // Processing based on main Keyword
                    $MainKeyword = $TokenizedMessage['MainKeyword'];

                    // Check if action = ANDROID (Argus synchronization)
                    if (CheckMainKeyword($bdd, $MainKeyword, '', 'global_keyword_android')) {
                        // Store the Android Version when synchronizing
                        $version = UpdateContactAndroidVersion($bdd, $ContactDetails, $TokenizedMessage);
                        // Modifying the ContactDetails "object"
                        $ContactDetails['AndroidVersion'] = $version;

                        $information = handle_action_template($bdd, $ContactDetails, $serverId, $logFileName);
                        $ProcessingDone = TRUE;

                    } else {

                        if (CheckMainKeywordTestMode($bdd, $MainKeyword, 'enable_alert', 'global_keyword_alert')) {
                            // Process as a special report with disease=alert
                            $TokenizedMessage['Collection'][GetMainKeyword($bdd, 'global_keyword_disease')] = $config["alert_reference"];

                            if (ConfigGetInteger($bdd, 'enable_test_mode') == 1) {
                                $TokenizedMessage['TestMode'] = TRUE;
                            }

                            $information = handle_action_report($bdd, $ContactDetails, $TokenizedMessage, $serverId, $logFileName);
                            $ProcessingDone = TRUE;
                            $smsStatus = IncomingSmsStatus::STATUS_PROCESSED;
                        }

                        if (CheckMainKeywordTestMode($bdd, $MainKeyword, 'enable_report', 'global_keyword_report')) {
                            if (ConfigGetInteger($bdd, 'enable_test_mode') == 1) {
                                $TokenizedMessage['TestMode'] = TRUE;
                            }

                            $information = handle_action_report($bdd, $ContactDetails, $TokenizedMessage, $serverId, $logFileName);
                            $ProcessingDone = TRUE;
                            $smsStatus = IncomingSmsStatus::STATUS_PROCESSED;
                        }

                        // Check if action = ALERT
                        if (CheckMainKeyword($bdd, $MainKeyword, 'enable_alert', 'global_keyword_alert')) {
                            // Process as a special report with disease=alert
                            $TokenizedMessage['Collection'][GetMainKeyword($bdd, 'global_keyword_disease')] = $config["alert_reference"];
                            $information = handle_action_report($bdd, $ContactDetails, $TokenizedMessage, $serverId, $logFileName);
                            $ProcessingDone = TRUE;
                            $smsStatus = IncomingSmsStatus::STATUS_PROCESSED;
                        }

                        // Check if action = REPORT
                        if (CheckMainKeyword($bdd, $MainKeyword, 'enable_report', 'global_keyword_report')) {
                            $information = handle_action_report($bdd, $ContactDetails, $TokenizedMessage, $serverId, $logFileName);
                            $ProcessingDone = TRUE;
                            $smsStatus = IncomingSmsStatus::STATUS_PROCESSED;
                        }

                    }
                } else {
                    $smsStatus = IncomingSmsStatus::STATUS_NOT_PROCESSED;

                    if (ConfigGetInteger($bdd, 'reponse_parsing_error') == 1) {
                        // Send reponse
                        $Response = sprintf(_("ARGUS: Your message is malformed [%s]"), $message_content);
                        $information = _("Response:") . ' ' . $Response;
                        SendSMS($bdd, $sender_number, $Response, $serverId, $logFileName);
                    } else {
                        // Do not respond
                        $information = _("Parsing error, no response as setup");
                    }
                }

                // Maybe it is a config Answer message
                if ($ProcessingDone == FALSE) {
                    // Nothing if config SMS
                    if (isConfigurationSms($message_content)) {
                        LogMessage('ConfigMessages', sprintf('Incoming Config SMS From %s, Gateway %s, Message : %s', $sender_number, $serverId, $message_content));
                        $ProcessingDone = TRUE;
                        $smsType = IncomingSmsType::TYPE_CONFIG;
                    }

                    $smsStatus = IncomingSmsStatus::STATUS_NOT_PROCESSED;
                }

                // Maybe it is an Odk Message ?
                if ($ProcessingDone == FALSE) {
                    $smsType = IncomingSmsType::TYPE_OTHER;
                    $smsStatus = IncomingSmsStatus::STATUS_NOT_PROCESSED;
                }

            } else {
                // Invalid contact
                $smsStatus = IncomingSmsStatus::STATUS_PHONE_NUMBER_UNKNOWN;

                if (ConfigGetInteger($bdd, 'reponse_unknown_contact') == 1) {
                    // Send reponse
                    $Response = $config["application_name"] . ": " . _("Your phone number is not registered in the system");
                    $information = _("Response:") . ' ' . $Response;
                    SendSMS($bdd, $sender_number, $Response, $serverId, $logFileName);
                } else {
                    // Do not respond
                    $information = _("Invalid contact, no response as setup");
                }
            }
        }

        // Log information regarding this processing
        $Message = sprintf(_("Processing SMS from %s: %s"), $sender_number, $information);
        // echo($Message."\n");
        LogMessage($logFileName, $Message);


        // End
        db_Close($bdd);

        $result = array(
            "success" => $resultValue,
            "error" => $information,
            "smsStatus" => $smsStatus,
            "smsType" => $smsType,
        );

        return $result;
    }

?>
