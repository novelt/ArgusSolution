<?php

	require_once("./tools/epidemiology.php");
	require_once("./tools/Utils/Constant.php");
	require_once("./tools/string_functions.php");


	// ============================
	// Handling REPORT/ALERT action
	// ============================
	function handle_action_report($bdd, $ContactDetails, $TokenizedMessage, $serverId, $logFileName)
    {
		global $config;
		$information="handle_action_report";
		$alert_reference = $config["alert_reference"] ;
		// ---------------------------------------------------------------
		// Check if special keywords must be sent back for the android app
		// ---------------------------------------------------------------
		$android_keyword_ok        = "";
		$android_keyword_threshold = "";
		global $message_identifier_value, $report_identifier_id;
		if (isset($message_identifier_value) && $message_identifier_value!="") {
			$android_keyword_ok        = $config["android_keyword_ok"]       ." ";
			$android_keyword_threshold = $config["android_keyword_threshold"]." ";
		}
		// -------------------------------------------
		// Search first for disease, year, month, week
		// -------------------------------------------

		$Disease="";
		$Week=0;
		$Month=0;
		$Year=0;

		GetMandatoryKeyWords($bdd, $Disease, $Week, $Month, $Year, $TokenizedMessage);

		// --------------------------
		// Checking the disease value
		// --------------------------
		if ($Disease=="") {
			// Error, no disease specified
			$response=sprintf(_("SES: No disease keyword found in the report [%s]"), $TokenizedMessage['Message']);
			// We stop here
			return SendSMSError($bdd, $ContactDetails['PhoneNumber'], $response, $serverId, $logFileName );
		}

		// Get Disease informations
		$disease_info = GetDiseaseInformations($bdd, $Disease);

		if (isset($disease_info) && count($disease_info)!=1) {
			// Error while identifying the disease
			$response=sprintf(_("SES: The disease reference can't be identified [%s]"), $TokenizedMessage['Message']);
			// We stop here
			return SendSMSError($bdd, $ContactDetails['PhoneNumber'], $response, $serverId, $logFileName );
		}

		$disease_reference=$disease_info[0]['disease'];
		$disease_name=$disease_info[0]['name'];

		// -------------------------------
		// Identifying and checking period
		// -------------------------------
		$PeriodFound="";
		$information = SetPeriodFromKeyWords($bdd, $PeriodFound, $disease_reference, $alert_reference, $Year, $Month, $Week, $TokenizedMessage['Message'], $ContactDetails['PhoneNumber'], $serverId, $logFileName);

		if ($information != "") {
			// We stop here
			return ($information);
		}

		// Translate period
		$PeriodLabel='';
		if ($PeriodFound==Constant::PERIOD_WEEKLY)  $PeriodLabel=_("Week");
		if ($PeriodFound==Constant::PERIOD_MONTHLY) $PeriodLabel=_("Month");

		// -------------------------------
		// Identifying StartPeriod
		// -------------------------------
		$StartPeriod = GetStartPeriodFromPeriodFound($PeriodFound, $Year, $Month, $Week);

		// -----------------------------------------
		// Get the values for the disease and period
		// -----------------------------------------
		$values = GetDiseasesValues($bdd, $disease_reference, $PeriodFound );

		// -------------------------------------
		// Check the values (mandatory/integer)
		// -------------------------------------
		$information = CheckConsistencyValues($bdd, $values, $TokenizedMessage, $ContactDetails['PhoneNumber'], $serverId, $logFileName);
		if ($information != "") {
			// We stop here
			return ($information);
		}

		// ----------------------------------------------------------------
		// Save the report if Android App is not in TEST mode
		// ----------------------------------------------------------------
        db_StartTransaction($bdd, __FUNCTION__, __LINE__, __FILE__);
		SaveDataReport($bdd, $TokenizedMessage, $ContactDetails, $values, $disease_reference, $PeriodFound, $StartPeriod, $report_identifier_id, $logFileName);
        db_CommitTransaction($bdd, __FUNCTION__, __LINE__, __FILE__);

		// -------------------------------------------------------------------------------------------------
		// Calculate, Check threshold, send alert to the sender and forward Thresholds to the recipient list
		// -------------------------------------------------------------------------------------------------
		$tresholdSent = CheckTreshold($bdd, $ContactDetails, $disease_reference, $disease_name, $PeriodFound, $StartPeriod, $PeriodLabel, $Week, $Month, $Year, $serverId, $android_keyword_threshold, $report_identifier_id, $logFileName);

		// ---------------
		// Send the report
		// ---------------
		$nb_recipients = 0 ;
		if (ConfigGetInteger ($bdd, 'enable_forward_data')==1){
			$nb_recipients = TransfertReportToRecipientsContacts($bdd, $values, $ContactDetails, $disease_reference, $alert_reference, $serverId, $logFileName);
		}

		// -----------------------
		// Send an acknowledgement
		// -----------------------
		if ($disease_reference==$alert_reference) {
			// Alert
			if ($nb_recipients==0) {
				$Response= ConfigGetString($bdd, 'server_alert_not_forwarded_ack');
				//$Response=_("SES: Alert was received but not forwarded to others contacts");
			} else {
				$Response=sprintf(ConfigGetString($bdd, 'server_alert_forwarded_ack'),$nb_recipients);
				//$Response=sprintf(_("SES: Alert was received and forwarded to %d contacts"),$nb_recipients);
			}
		} else {
			// Report
			//$Response=sprintf(_("SES: Report for disease %s starting on %s (%s) was received"),$disease_name, date("d/m/Y",$StartPeriod), $PeriodLabel);
			$Response=sprintf(ConfigGetString($bdd, 'server_report_received_ack'), $PeriodLabel, $disease_reference, date("d/m/Y",$StartPeriod));
		}
		// Add the android keyword if defined
		$Response=$android_keyword_ok.$Response;
		// Only send ack message if asked
		$information=_("Response:").' '.$Response;

		if (ConfigGetInteger ($bdd, 'enable_report_ack')==1) {

			// Threshold not sent so we need to send an ACK message
			if ($tresholdSent == FALSE) {
				SendSMS($bdd, $ContactDetails['PhoneNumber'], $Response, $serverId, $logFileName);
			}
			else{
				// Threshold sent so we don't need to send an ACK message
				$information.=" - "._("Ack not sent because threshold was sent");
			}
		} else {
			$information.=" - "._("Ack not sent (disabled)");
		}
		return($information);
	}

	// ========================
	// Handling TEMPLATE action
	// ========================
	function handle_action_template($bdd, $ContactDetails, $serverId, $logFileName) {

		global $config;
		// Check if a message identifier must be sent back
		global $message_identifier_value, $message_identifier_receiver;
		//$information="handle_action_template";
		//Get the Max Size for a SMS
		$MaxSize=ConfigGetInteger ($bdd, 'max_length_sms_sent');
		// List of periods
		$PeriodList=array();
        $PeriodList[]=Constant::PERIOD_NONE;
		$PeriodList[]=Constant::PERIOD_WEEKLY;
		$PeriodList[]=Constant::PERIOD_MONTHLY;

		//List of SMS
		$SmsToSend = array();

		// Get Concerned Diseases
		$diseases = GetDiseases($bdd);

		$nb_template=0;
		for ($i=0; $i<count($diseases); $i++) {
			for ($j=0; $j<count($PeriodList); $j++) {
				// Check if we have values for the period asked
				$Disease=$diseases[$i]['disease'];
				$DiseaseName=$diseases[$i]['name'];
				$DiseaseKeywords=GetPrimaryKeyword($diseases[$i]['keywords']);
				$PeriodFound=$PeriodList[$j];

				if (isset($diseases[$i][$PeriodFound]) && intval($diseases[$i][$PeriodFound])>0) {
					// Prepare KeyWord To Send
					$KeyWordToSend = array() ;

					// Formatting response
					$Response="";

                    if ($PeriodFound==Constant::PERIOD_NONE) {
                        $Response.=Constant::TEMPLATE_ALERT.": ".GetMainKeyword($bdd, 'global_keyword_alert')      .Constant::SMS_SPACE;
                    }
                    if ($PeriodFound==Constant::PERIOD_WEEKLY) {
                        $Response.= Constant::TEMPLATE_WEEKLY.": ".GetMainKeyword($bdd, 'global_keyword_report')   .Constant::SMS_SPACE;
                        $Response.= GetMainKeyword($bdd, 'global_keyword_disease')."=".$DiseaseKeywords.Constant::SMS_SEPARATOR;
                        // Adding Disease Label
                        $KeyWordToSend[] = Constant::KEYWORD_HEALTHFACILITY_LABEL."=".$DiseaseName ;
                        $KeyWordToSend[] = GetMainKeyword($bdd, 'global_keyword_year')   ."=".Constant::GetTruncatedType(Constant::TYPE_INTEGER);
                        $KeyWordToSend[] = GetMainKeyword($bdd, 'global_keyword_week')   ."=".Constant::GetTruncatedType(Constant::TYPE_INTEGER);
                    }
                    if ($PeriodFound==Constant::PERIOD_MONTHLY) {
                        $Response.= Constant::TEMPLATE_MONTHLY.": ".GetMainKeyword($bdd, 'global_keyword_report')  .Constant::SMS_SPACE;
                        $Response.= GetMainKeyword($bdd, 'global_keyword_disease')."=".$DiseaseKeywords.Constant::SMS_SEPARATOR;
                        // Adding Disease Label
                        $KeyWordToSend[] = Constant::KEYWORD_HEALTHFACILITY_LABEL."=".$DiseaseName ;
                        $KeyWordToSend[] = GetMainKeyword($bdd, 'global_keyword_year')   ."=".Constant::GetTruncatedType(Constant::TYPE_INTEGER);
                        $KeyWordToSend[] = GetMainKeyword($bdd, 'global_keyword_month')  ."=".Constant::GetTruncatedType(Constant::TYPE_INTEGER);
                    }

					// Get Disease Values
					$values = GetDiseasesValues($bdd, $Disease, $PeriodFound);

					// Complete the KeyWord tab with values from diseases values
					CompleteKeyWordTabWithDiseaseValues($KeyWordToSend, $values);

					// Complete the KeyWord tab with constraints
					CompleteKeyWordTabWithConstraints($bdd, $Disease, $PeriodFound, $KeyWordToSend, $logFileName);

					$GlobalRemainingSize= $MaxSize - strlen($Response) ;
					if (isset($message_identifier_value) && isset($message_identifier_receiver) && $ContactDetails['PhoneNumber']==$message_identifier_receiver) {
						// We need take into account length of $message_identifier_value
						$GlobalRemainingSize = $GlobalRemainingSize - strlen($message_identifier_value);
					}

					// Create List of SMS to send regarding the Max length of a SMS
					$SmsToSend = array_merge($SmsToSend, CreateSmsToSend($KeyWordToSend, $GlobalRemainingSize, $Response)) ;

				}
			}
		}
		// Adding the Android app configuration
        // Parameters: M4ConfAlert
        $SmsToSend[]=Constant::TEMPLATE_CONF.": M4ConfAlert=".ConfigGetString($bdd, 'android_app_confirmation_alert');

        // Parameters: M4ConfW
        $SmsToSend[]=Constant::TEMPLATE_CONF.": M4ConfW=".ConfigGetString($bdd, 'android_app_confirmation_week');

        // Parameters: M4ConfM
        $SmsToSend[]=Constant::TEMPLATE_CONF.": M4ConfM=".ConfigGetString($bdd, 'android_app_confirmation_month');

        // Parameters: Health Facility Name
        // ARGUS Modification 2016-01-13
        $Response=Constant::TEMPLATE_CONF.": HFName=".$ContactDetails['SiteName'].Constant::SMS_SEPARATOR;
        // Add Servers phone numbers here as we will have more place in this SMS
        $Response.="Server=".str_replace(" ","",str_replace(",","/",ConfigGetString($bdd, 'server_phone_number')));

        $SmsToSend[]=$Response;

        // Parameters: Final SMS
        $Response =Constant::TEMPLATE_CONF.": ";
        $Response.="NbMsg=".(count($SmsToSend) +1).Constant::SMS_SEPARATOR;
        $Response.="NbCharMax=".ConfigGetInteger($bdd, 'max_length_sms_sent').Constant::SMS_SEPARATOR;
        $Response.="WeekStart=".$config["epi_first_day"].Constant::SMS_SEPARATOR;
        $Response.="D4ConfAlert=".ConfigGetInteger($bdd, 'android_app_confirmation_alert').Constant::SMS_SEPARATOR;
        $Response.="D4ConfW=".ConfigGetInteger($bdd, 'android_app_confirmation_week').Constant::SMS_SEPARATOR;
        $Response.="D4ConfM=".ConfigGetInteger($bdd, 'android_app_confirmation_month');

        $SmsToSend[]=$Response;

		// Send templates
		for($k=0; $k<count($SmsToSend); $k++) {
            SendSMS($bdd, $ContactDetails['PhoneNumber'], $SmsToSend[$k], $serverId, $logFileName);
        }

		// Check if templates were sent
		if (count($SmsToSend)>0) {
			// Templates sent
			$information=sprintf(_("%d templates has been sent"), $nb_template);
		} else {
			// No template, send response
			$Response=_("SES: No item found for the asked template");
			$information=_("Response:").' '.$Response;
			SendSMS ($bdd, $ContactDetails['PhoneNumber'], $Response, $serverId, $logFileName);
		}

		return($information);
	}


	/**
	 * @param $bdd
	 * @return array of disease
	 *
	 * Return diseases with periodicity
	 */
	function GetDiseases($bdd)
    {
		// Get the disease list with their periodicity
		$SQL="
					SELECT
						d.disease,
						d.name,
						d.keywords,
						SUM(CASE WHEN period='None'    THEN 1 ELSE 0 END) As 'None',
						SUM(CASE WHEN period='Weekly'  THEN 1 ELSE 0 END) As 'Weekly',
						SUM(CASE WHEN period='Monthly' THEN 1 ELSE 0 END) As 'Monthly'
					FROM
						ses_diseases d
						INNER JOIN ses_disease_values v ON d.disease=v.disease
					GROUP BY
						d.disease
					ORDER BY
						None DESC, d.disease ASC, Weekly DESC
				;";
		$diseases=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

		return $diseases ;
	}

	/**
	 * @param $bdd
	 * @param $disease
	 * @param $periodFound
	 * @return array of values
	 *
	 * Return Disease values
	 */
	function GetDiseasesValues($bdd, $disease, $periodFound)
    {
		// Get the disease values
		$SQL="
							SELECT
								v.value,
								v.datatype,
								v.keywords,
								v.mandatory
							FROM
								ses_disease_values v
							WHERE
								v.disease='".$bdd->escape($disease)."'
								AND
								v.period='".$bdd->escape($periodFound)."'
							ORDER BY
								v.position ASC
						;";

		$values=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

		return $values ;
	}

	/**
	 * @param $keyWordToSend
	 * @param $globalRemainingSize
	 * @param $response
	 * @return array of Sms to send
	 *
	 * Return an array of SMS to send
	 */
	function CreateSmsToSend($keyWordToSend, $globalRemainingSize, $response)
    {
		$remainingSize = $globalRemainingSize;
		$nextSms = "";
		$smsToSend = array() ;

		$k=0 ;
		while ($k<count($keyWordToSend)){

			$nextKeyWord = $keyWordToSend[$k] ;
			$nextKeyWordLength = strlen($nextKeyWord);
			$justOneKeyWord = TRUE ;

			if (strlen($nextSms) != 0)	{
				$nextKeyWord = Constant::SMS_SEPARATOR.$nextKeyWord ;
				$nextKeyWordLength = strlen($nextKeyWord);
				$justOneKeyWord = FALSE ;
			}

			if ($remainingSize >= $nextKeyWordLength) {
				$remainingSize = $remainingSize - $nextKeyWordLength ;
				$nextSms.= $nextKeyWord ;
				$k++ ;
			}
			else {

				if ($justOneKeyWord){ // SPECIAL CASE : First KeyWORD must be the LBL of the disease. If it is too long, we must truncate the disease Label
					$nextSms = substr($keyWordToSend[$k], 0, $remainingSize);
					$k++ ;
				}

				$smsToSend[] = $response . $nextSms;
				$nextSms = "";
				$remainingSize = $globalRemainingSize;
			}
		}

		if (strlen($nextSms) > 0) {
			$smsToSend[] = $response . $nextSms;
		}

		return $smsToSend ;
	}

	/**
	 * @param $keyWordToSend
	 * @param $values
	 *
	 * Add keyword from disease values to $keyWordToSend array
	 */
	function CompleteKeyWordTabWithDiseaseValues(&$keyWordToSend, $values)
    {
		// Get Keywords Table
		for ($k=0; $k<count($values); $k++) {
			$tempKeyWord = "";
			$tempKeyWord.=GetPrimaryKeyword($values[$k]['keywords']);

			$tempKeyWord.="=";

            // Send the datatype for Android
            $datatype = $values[$k]['datatype'];
            $tempKeyWord.=Constant::GetTruncatedType($datatype);

            // Send the mandatory flag : No Flag means mandatory, Flag 0 means not mandatory
            $mandatory = $values[$k]['mandatory'] ;
            if ($mandatory == "0") {
                $tempKeyWord .= Constant::SMS_SPACE . $mandatory;
            }

			$keyWordToSend[] = $tempKeyWord ;
		}
	}

	/**
	 * @param $bdd
	 * @param $disease
	 * @param $periodFound
	 * @param $keyWordToSend
	 *
	 * Create Constraints informations keyWords to add in SMS synch
	 */
	function CompleteKeyWordTabWithConstraints($bdd, $disease, $periodFound, &$keyWordToSend, $logFileName)
    {
		$constraints = GetConstraintsInformations($bdd, $disease, $periodFound);

		if (isset($constraints) && count($constraints) > 0)
		{
			for ($k=0; $k<count($constraints); $k++) {
				$tempKeyWord = "";

				$keyWordFrom = GetPrimaryKeyword($constraints[$k]['keywords_from']);
				$keyWordTo = GetPrimaryKeyword($constraints[$k]['keywords_to']);
				$operator = Constant::GetOperatorFromString($constraints[$k]['operator']);

				if (isset($keyWordFrom) && isset($keyWordTo) && isset($operator) && $keyWordFrom!='' && $keyWordTo!='' && $operator!=''){
					$tempKeyWord.=$keyWordFrom;
					$tempKeyWord.=$operator;
					$tempKeyWord.=$keyWordTo;

					$keyWordToSend[] = $tempKeyWord ;
					$message = sprintf('Constraint for disease %s : %s', $disease, $tempKeyWord);
					LogMessage($logFileName, $message);
				}
				else
				{
					$message = sprintf('Error when creating constraint for disease %s, with keyword From %s, keyword To %s, Operator %s', $keyWordFrom, $keyWordTo, $operator);
					LogMessage($logFileName, $message);
				}
			}
		}
	}

	/**
	 * @param $bdd
	 * @param $disease
	 * @param $periodFound
	 * @return array
	 *
	 * Get Constraint informations defined for a specific disease & period
	 */
	function GetConstraintsInformations($bdd, $disease, $periodFound)
    {
		$SQL="
				SELECT
					c.value_from,
					c.operator,
					c.value_to,
					v1.keywords as keywords_from,
					v2.keywords as keywords_to
				FROM
					ses_disease_constraints c
				LEFT JOIN ses_disease_values v1	ON c.value_from = v1.value AND c.disease = v1.disease AND c.period = v1.period
				LEFT JOIN ses_disease_values v2	ON c.value_to = v2.value AND c.disease = v2.disease AND c.period = v2.period
				WHERE
					c.disease = '".$bdd->escape($disease)."'
				AND v1.disease = '".$bdd->escape($disease)."'
				AND v2.disease = '".$bdd->escape($disease)."'
				AND c.period = '".$bdd->escape($periodFound)."'
				AND v1.period = '".$bdd->escape($periodFound)."'
				AND v2.period = '".$bdd->escape($periodFound)."'
			;";

		$constraints=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

		return $constraints ;
	}

	/**
	 * @param $bdd
	 * @param $disease
	 * @return array
	 *
	 * Return Disease informations
	 */
	function GetDiseaseInformations($bdd, $disease)
    {

		$SQL="
				SELECT
					disease,
					name
				FROM
					ses_diseases
				WHERE
					keywords='".$bdd->escape($disease)."'
					OR
					keywords LIKE '".$bdd->escape($disease).",%'
					OR
					keywords LIKE '%,".$bdd->escape($disease)."'
					OR
					keywords LIKE '%,".$bdd->escape($disease).",%'
			;";

		$disease_info=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

		return $disease_info;
	}

	/**
	 * @param $bdd
	 * @param $disease
	 * @param $week
	 * @param $month
	 * @param $year
	 * @param $tokenizedMessage
	 *
	 * Set $disease, $week, $month, $year variables from received message
	 */
	function GetMandatoryKeyWords($bdd, &$disease, &$week, &$month, &$year, &$tokenizedMessage)
    {
		$removeKeys=array();

		foreach ($tokenizedMessage['Collection'] as $key=>$value) {
			// Disease
			if ($disease=="" && CheckMainKeyword($bdd, $key, '', 'global_keyword_disease')==TRUE) {
				$disease=$value;
				$removeKeys[]=$key;
			}
			// Year
			if ($year==0 && CheckMainKeyword($bdd, $key, '', 'global_keyword_year')==TRUE) {
				$year=intval($value);
				$removeKeys[]=$key;
			}
			// Week
			if ($week==0 && CheckMainKeyword($bdd, $key, '', 'global_keyword_week')==TRUE) {
				$week=intval($value);
				$removeKeys[]=$key;
			}
			// Month
			if ($month==0 && CheckMainKeyword($bdd, $key, '', 'global_keyword_month')==TRUE) {
				$month=intval($value);
				$removeKeys[]=$key;
			}
		}
		// Remove already processed keys - Keep only values
		for ($i=0; $i<count($removeKeys); $i++) {
			unset($tokenizedMessage['Collection'][$removeKeys[$i]]);
		}
	}

    /**
     * Set the $period variable and check if $year, $month and $week have possible values
     *
     * @param $bdd
     * @param $periodFound
     * @param $disease_reference
     * @param $alert_reference
     * @param $year
     * @param $month
     * @param $week
     * @param $message
     * @param $phone_number
     * @param $serverId
     * @param $logFileName
     *
     * @return string
     */
	function SetPeriodFromKeyWords($bdd, &$periodFound, $disease_reference, $alert_reference, $year, $month, $week, $message, $phone_number, $serverId, $logFileName )
    {
		$information = "";

		if ($disease_reference==$alert_reference && $year==0 && $month==0 && $week==0) {
			$periodFound=Constant::PERIOD_NONE;
		} elseif ($disease_reference!=$alert_reference && $year!=0 && $week!=0 && $month==0) {
			$periodFound=Constant::PERIOD_WEEKLY;
		} elseif ($disease_reference!=$alert_reference && $year!=0 && $month!=0 && $week==0) {
			$periodFound=Constant::PERIOD_MONTHLY;
		} else {
			// Error - Wrong period definition
			if ($disease_reference==$alert_reference) {
				$response=sprintf(_("SES: No year, month or week keywords are allowed with an alert! [%s]"), $message);
			} else {
				$response=sprintf(_("SES: Year must be present then only week or month [%s]"), $message);
			}
			return SendSMSError($bdd, $phone_number, $response, $serverId, $logFileName);
		}

		// Check the year
		if ($year!=0 && !($year>1900 && $year<2099)) {
			// Error, bad year
			$response=sprintf(_("SES: Year must be correctly formatted with 4 valid digits [%s]"), $message);
			return SendSMSError($bdd, $phone_number, $response, $serverId, $logFileName);
		}
		// Check the month
		if ($month!=0 && !($month>=1 && $month<=12)) {
			// Error, bad month
			$response=sprintf(_("SES: Month must be a numeric value between 1 and 12 [%s]"), $message);
			return SendSMSError($bdd, $phone_number, $response, $serverId, $logFileName);
		}
		// Check the week
		if ($week!=0 && !($week>=1 && $week<=53)) {
			// Error, bad week
			$response=sprintf(_("SES: Week must be a numeric value between 1 and 53 [%s]"), $message);
			return SendSMSError($bdd, $phone_number, $response, $serverId, $logFileName);
		}

		return $information ;
	}

	/**
	 * @param $bdd
	 * @param $phoneNumber
	 * @param $response
	 * @param $serverId
	 * @return string
	 *
	 * Send SMS relating the error
	 */
	function SendSMSError($bdd, $phoneNumber, $response, $serverId, $logFileName )
    {
		$information=_("Response:").' '.$response;
		SendSMS ($bdd, $phoneNumber, $response, $serverId, $logFileName);
		return($information);
	}

	/**
	 * @param $bdd
	 * @param $week
	 * @param $month
	 * @param $year
	 * @param $periodFound
	 * @param $sitePath
	 * @param $disease_reference
	 * @return array
	 *
	 * Get Threshold informations
	 */
	function GetThresholdInfo($bdd, $periodFound, $week, $month, $year, $sitePath, $disease_reference )
    {
		$thresholds_info = null ;

		$where = "";
		$whereNull = "";
		if ($periodFound==Constant::PERIOD_WEEKLY) {
			$where = "AND weekNumber =" . $week;
			$whereNull = "AND weekNumber is NULL ";
		}
		else if ($periodFound==Constant::PERIOD_MONTHLY) {
			$where = "AND monthNumber =" . $month;
			$whereNull = "AND monthNumber is NULL ";
		}
		else {
			return null;
		}

		// Find the values
		$SQL="
				SELECT
					`maxValue`,
					`values`
				FROM
					ses_thresholds
				WHERE
					path='".$bdd->escape($sitePath)."'
					AND
					disease='".$bdd->escape($disease_reference)."'
					AND
					period='".$bdd->escape($periodFound)."'
					AND
					year=".$year." ";

		$SQLWhere = $SQL." ".$where." ;";
		$SQLWhereNull = $SQL." ".$whereNull." ;";

		$thresholds=db_GetArray($bdd,$SQLWhere,__FUNCTION__,__LINE__,__FILE__);
		if (!isset($thresholds) || count($thresholds)==0) {
			// Threshold not found

			// Check if there is a more Generic Threshold for full year for example
			$thresholds=db_GetArray($bdd,$SQLWhereNull,__FUNCTION__,__LINE__,__FILE__);

			if (!isset($thresholds) || count($thresholds)==0) {
				// Threshold not found at this level. Check level parent
				// Get ParentPath from sitepath
				$parent = GetParentPathFromPath($sitePath);
				// Check threshold
				if (isset($parent) && $parent!=null && $parent!==FALSE && $parent!='' ){
					//Recursive call to same function
					$thresholds_info = GetThresholdInfo($bdd, $periodFound, $week, $month, $year, $parent, $disease_reference);
				}
			}
		}

		if (isset($thresholds) && count($thresholds)>=1) {
			for($i = 0 ; $i < count($thresholds) ; $i++) {
				$thresholds_info[$i]['values'] = explode(",", $thresholds[$i]['values']);
				$thresholds_info[$i]['max'] = intval($thresholds[$i]['maxValue']);
			}
		}

		return $thresholds_info ;
	}

	/**
	 * @param $PeriodFound
	 * @param $year
	 * @param $month
	 * @param $week
	 * @return int|null
	 *
	 * Return StartPeriod regarding the Period
	 */
	function GetStartPeriodFromPeriodFound($PeriodFound, $year, $month, $week)
    {
		switch($PeriodFound)
		{
			case Constant::PERIOD_WEEKLY :
				return Epi2Timestamp($year, $week);
				break ;
			case Constant::PERIOD_MONTHLY :
				return mktime(0, 0, 0, $month, 1, $year);
				break ;
			default :
				return time();
				break;
		}
	}

    /**
     * Check if received data are consistent
     *
     * @param $bdd
     * @param $values
     * @param $tokenizedMessage
     * @param $phoneNumber
     * @param $serverId
     * @param $logFileName
     *
     * @return string
     */
	function CheckConsistencyValues($bdd, &$values, &$tokenizedMessage, $phoneNumber, $serverId, $logFileName)
    {
		$information = "";
		$error_list="";

		for ($i=0; $i<count($values); $i++) {
			// Trying to get received data for each value
			$keywords=explode(",", $values[$i]['keywords']);
			for ($j=0; $j<count($keywords); $j++) {
				if (isset($tokenizedMessage['Collection'][$keywords[$j]])) {
					// Value found
					$values[$i]['received']=$tokenizedMessage['Collection'][$keywords[$j]];
					// Remove value
					unset($tokenizedMessage['Collection'][$keywords[$j]]);
				}
			}
			// Check if value is mandatory and nothing received
			if (intval($values[$i]['mandatory'])==1 && isset($values[$i]['received'])==FALSE) {
				// Missing mandatory value
				// As we can have disease data in multiples SMS, we need to remove this test
				// if ($error_list!="") $error_list.=" , ";
				// $error_list.=sprintf(_("value [%s] is missing"), $values[$i]['value']);

				// Check if integer value is correctly formatted and non-negative
				if ($values[$i]['datatype']==Constant::TYPE_INTEGER && isset($values[$i]['received'])==TRUE && (is_numeric($values[$i]['received'])==FALSE || (is_numeric($values[$i]['received'])==TRUE && intval($values[$i]['received'])<0))) {
					// Wrong integer value
					if ($error_list!="") $error_list.=" , ";
					$error_list.=sprintf(_("value [%s] must be a non-negative integer"), $values[$i]['value']);
				}
			}
		}
		if ($error_list!="") {
			// Error found in values
			$response=sprintf(_("SES: The report contains errors in values: %s [%s]"), $error_list, $tokenizedMessage['Message']);
			// We stop here
			return SendSMSError($bdd, $phoneNumber, $response, $serverId, $logFileName);
		}

		$unexpected_keywords=array();
		foreach ($tokenizedMessage['Collection'] as $Key=>$Value) {
			$unexpected_keywords[]=$Key;
		}

		if (count($unexpected_keywords)>0) {
			// Error, there are some unexpected keywords!
			$response=sprintf(_("SES: The report contains unexpected keywords: %s [%s]"), implode(",", $unexpected_keywords),$tokenizedMessage['Message']);
			// We stop here
			return SendSMSError($bdd, $phoneNumber, $response, $serverId, $logFileName);
		}

		return $information;
	}

	/**
	 * @param BDD $bdd
	 * @param $tokenizedMessage
	 * @param $contactDetails
	 * @param $values
	 * @param $disease_reference
	 * @param $periodFound
	 * @param $report_identifier_id
	 * @param $startPeriod
	 *
	 * Save Data report into Data Base if needed
	 */
	function SaveDataReport($bdd, $tokenizedMessage, $contactDetails, $values, $disease_reference, $periodFound, $startPeriod, $report_identifier_id, $logFileName)
    {
		if ( isset($tokenizedMessage['TestMode']) && $tokenizedMessage['TestMode'] == TRUE  ) {
			LogMessage($logFileName, "Test Mode Enabled on Android Device : No Data will be saved");
		}
		else {
		    $sesDataId = null;

		    $existingReports = GetExistingDiseaseReport($bdd, $contactDetails['SitePath'], $contactDetails['PhoneNumber'], $disease_reference, $periodFound, $startPeriod, $report_identifier_id);

            // Common values
            $data = array(
                "period" => "'" . $bdd->escape($periodFound) . "'",
                "path" => "'" . $bdd->escape($contactDetails['SitePath']) . "'",
                "reception" => "'" . date("Y-m-d H:i:s") . "'",
                "contactName" => "'" . $bdd->escape($contactDetails['Name']) . "'",
                "contactPhoneNumber" => "'" . $bdd->escape($contactDetails['PhoneNumber']) . "'",
                "disease" => "'" . $bdd->escape($disease_reference) . "'",
                "periodStart" => "'" . date("Y-m-d H:i:s", $startPeriod) . "'",
                "reportId" => $report_identifier_id == null ? "NULL" : $report_identifier_id,
            );

            // Insertion
            if (!isset($existingReports) || $existingReports == null || count($existingReports) == 0) {
                db_Insert($bdd, __FUNCTION__, __LINE__, __FILE__, "ses_data", $data);
                $sesDataId = db_LastInsertedId($bdd, __FUNCTION__, __LINE__, __FILE__);
            } else {
                $sesDataId = $existingReports[0]['id'];
            }

            for ($i = 0; $i < count($values); $i++) {
                if (isset($values[$i]['received']) && isset($values[$i]['value'])) {
                    // Suppression ancienne(s) valeur(s) si elle(s) existe(nt)
                    db_Delete($bdd, __FUNCTION__, __LINE__, __FILE__, "ses_datavalues", "FK_dataId = " . $sesDataId. " AND `key` ='" .$values[$i]['value']. "'");

                    $value =    ["FK_dataId" => $sesDataId,
                                 "`key`" => "'".$values[$i]['value']."'",
                                "value" => "'". $bdd->escape($values[$i]['received'])."'"
                    ];
                    db_Insert($bdd, __FUNCTION__, __LINE__, __FILE__, "ses_datavalues", $value);

                }
            }

            $List = array(
                "exported" => "NULL"
            );

            // Export a complete report for this disease (all the values)
            db_Update($bdd, __FUNCTION__, __LINE__, __FILE__, "ses_datavalues", $List, "FK_dataId = " . $sesDataId);
        }
	}

    /**
     * Get Old entry for same report, disease
     *
     * @param $bdd
     * @param $sitePath
     * @param $phoneNumber
     * @param $disease_reference
     * @param $periodFound
     * @param $startPeriod
     * @param $report_identifier_id
     * @return array|null
     */
	function GetExistingDiseaseReport($bdd, $sitePath, $phoneNumber, $disease_reference, $periodFound, $startPeriod, $report_identifier_id)
    {
		// We can now receive report in multiples messages
		// So we retrieve last one receive for same
		// - Path
		// - contactPhoneNumber
		// - disease
		// - period
		// - periodStart
		//
		// Then we create a new one entry with last key value pair updated if needed and new key values

        if (!isset($report_identifier_id) || $report_identifier_id == null) {
           return null;
        }

		$SQL="
							SELECT
								d.id, v.`key`, v.value
							FROM
								ses_data d INNER JOIN ses_datavalues v ON d.id = v.FK_dataId
							WHERE
								d.path='".$bdd->escape($sitePath)."'
								AND
								d.contactPhoneNumber='".$bdd->escape($phoneNumber)."'
								AND
								d.disease='".$bdd->escape($disease_reference)."'
								AND
								d.period='".$bdd->escape($periodFound)."'
								AND
								d.periodStart='".date("Y-m-d H:i:s", $startPeriod)."' 
		                        AND
                                d.reportId ='".$report_identifier_id."' ";

		$oldEntries = db_GetArray($bdd,$SQL ,__FUNCTION__,__LINE__,__FILE__);

		return $oldEntries;
	}

    /**
     * Get recipient for ALERT only and transfer ALERT.
     *
     * @param $bdd
     * @param $values
     * @param $contactDetails
     * @param $disease_reference
     * @param $alert_reference
     * @param $serverId
     * @param $logFileName
     *
     * @return int
     */
	function TransfertReportToRecipientsContacts($bdd, $values, $contactDetails, $disease_reference, $alert_reference, $serverId, $logFileName)
    {
		if ($disease_reference==$alert_reference) {
			// Alert
			$messageType="ALERT";
			//$messageForRecipients=sprintf(_("SES: Alert from site %s (%s): "), $contactDetails['SiteReference'], $contactDetails['PhoneNumber']);
			$messageForRecipients=sprintf(ConfigGetString($bdd, 'server_alert_forward'), $contactDetails['SiteReference'], $contactDetails['PhoneNumber']);
		}
		else {
            return 0;
        }

		// Get the values received
		$nb_value=1;
		for ($i=0; $i<count($values); $i++) {
			if (isset($values[$i]['received'])==TRUE) {
				if ($nb_value>1) $messageForRecipients.=", ";
				// Case when the label of value are their reference
				// $MessageForRecipients.=$values[$i]['value']."=".$values[$i]['received'];
				// Case when the label of value are their main keyword
				$messageForRecipients.=GetPrimaryKeyword($values[$i]['keywords'])."=".$values[$i]['received'];
				$nb_value++;
			}
		}

		$recipients = GetRecipientsPhoneNumbers($bdd, $contactDetails, $messageType);

		for ($i=0; $i<count($recipients); $i++) {
            $alertPreferredGateway = GetAlertPreferredGateway($bdd, $recipients[$i]);
			// Send to each recipient
			SendSMS ($bdd, $recipients[$i]['phoneNumber'], $messageForRecipients, ($alertPreferredGateway !== null ? $alertPreferredGateway : $serverId), $logFileName);
		}
		$nb_recipients=count($recipients);

		return $nb_recipients ;
	}

    /**
     * Get the Alert Preferred Gateway for a recipient if this gateway Number exists as a gateway device
     * Try to find the gateway for the contact , then recursively from sites
     *
     * @param $bdd
     * @param $recipient
     * @return null
     */
	function GetAlertPreferredGateway($bdd, $recipient)
    {
        $alertPreferredGateway = $recipient['alertPreferredGateway'];
        if ($alertPreferredGateway !== NULL) {
            if (is_a_gatewayNumber($bdd, $alertPreferredGateway)) {
                return $alertPreferredGateway;
            }
        }

        return GetAlertPreferredGatewayFromSite($bdd, $recipient['group_path']);

    }

    /***
     * Return the Alert Preferred Gateway for a site path
     *
     * @param $bdd
     * @param $path
     * @return null
     */
    function GetAlertPreferredGatewayFromSite($bdd, $path)
    {
        $SQL="
				SELECT
				    fg.ses_alert_preferred_gateway,
				    fg.parentPath
				FROM
					frontline_group fg
				WHERE
					fg.path='".$bdd->escape($path)."'
			;";

        $results = db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

        if (isset($results[0])) {
            $ses_alert_preferred_gateway = $results[0]['ses_alert_preferred_gateway'];
            $parentPath = $results[0]['parentPath'];

            if ($ses_alert_preferred_gateway !== null) {
                if (is_a_gatewayNumber($bdd, $ses_alert_preferred_gateway)) {
                    return $ses_alert_preferred_gateway;
                }
            }

            if (isset($parentPath) && $parentPath != '' && $parentPath != null) {
                return GetAlertPreferredGatewayFromSite($bdd, $parentPath);
            }
        }

        return null ;
    }

    /**
     * @param $bdd
     * @param $contactDetails
     * @param $messageType
     * @return array
     */
	function GetRecipientsPhoneNumbers($bdd, $contactDetails, $messageType)
    {
        // If Site as cascading Alert, we get all contacts from parent Sites
        if ($contactDetails["CascadingAlert"] == TRUE) {
            return GetCascadingRecipientsPhoneNumbers($bdd, $contactDetails, $contactDetails['SiteParentPath']);
        } else {
            return GetConfiguredRecipientsPhoneNumbers($bdd, $contactDetails, $messageType);
        }
    }

    function GetCascadingRecipientsPhoneNumbers($bdd, $contactDetails, $sitePath)
    {
        $SQL = "
                SELECT  c.contact_id,
                        c.phoneNumber,
                        c.alertPreferredGateway,
                        gm.group_path,
                        fg.parentPath
                FROM contact c 
                     INNER JOIN groupmembership gm ON c.contact_id = gm.contact_contact_id
                     INNER JOIN frontline_group fg ON fg.path = gm.group_path
                WHERE fg.path='".$bdd->escape($sitePath)."'
                ";

        $recipients=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

        // Get the Parent.
        if (isset($recipients[0])) {
            $parentPath = $recipients[0]['parentPath'];

            if (isset($parentPath) && $parentPath != '' && $parentPath != null) {
                $recipients = array_merge($recipients, GetCascadingRecipientsPhoneNumbers($bdd, $contactDetails, $parentPath));
            }
        }

        return $recipients;
    }

    function GetConfiguredRecipientsPhoneNumbers($bdd, $contactDetails, $messageType)
    {
		// Get the contacts phone number
		$SQL="
				SELECT
				    c.contact_id, 
					c.phoneNumber,
					c.alertPreferredGateway,
					gm.group_path
				FROM
					ses_recipients r
					INNER JOIN groupmembership gm ON r.pathRecipients=gm.group_path
					INNER JOIN contact c ON gm.contact_contact_id=c.contact_id
				WHERE
					r.path='".$bdd->escape($contactDetails['SitePath'])."'
					AND
					r.messageType='".$messageType."'
			;";

		$recipients=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

		return $recipients ;
	}


	/**
	 * @param $bdd
	 * @param $contactDetails
	 * @param $disease_reference
	 * @param $disease_name
	 * @param $periodFound
	 * @param $startPeriod
	 * @param $periodLabel
	 * @param $week
	 * @param $month
	 * @param $year
	 * @param $serverId
	 * @param $android_keyword_threshold
	 * @param $report_identifier_id
	 * @param $logFileName
	 *
	 * @return true if threshold is sent
	 *
	 * Check Threshold and Send Threshold SMS to concerned health Facility and Send Threshold notifications to recipients
	 */
	function CheckTreshold($bdd, $contactDetails, $disease_reference, $disease_name, $periodFound, $startPeriod, $periodLabel, $week, $month, $year, $serverId, $android_keyword_threshold, $report_identifier_id, $logFileName)
    {
		// -------------------------------------
		// Get the thresholds values and maximum
		// -------------------------------------
		$thresholds_info = GetThresholdInfo($bdd, $periodFound, $week, $month, $year, $contactDetails['SitePath'], $disease_reference );
		$thresholds_sum=0;

		if (!isset($thresholds_info)) {
			return false;
		}

		//Get Last entry for this disease
        $existingReports = GetExistingDiseaseReport($bdd, $contactDetails['SitePath'], $contactDetails['PhoneNumber'], $disease_reference, $periodFound, $startPeriod, $report_identifier_id);

		if (!isset($existingReports) || $existingReports == null || count($existingReports) == 0) {
			return false;
		}

		for ($t = 0 ; $t < count($thresholds_info) ; $t ++) {
			$thresholds_values = $thresholds_info[$t]['values'];
			$thresholds_max = $thresholds_info[$t]['max'];

			//TODO : Check if last entry is complete
			//TODO : Check if the threshold message has already not been sent

            for ($i = 0; $i < count($existingReports); $i++) {
                if (isset($existingReports[$i]['key']) && isset($existingReports[$i]['value'])) {
                    $oldKey = $existingReports[$i]['key'];
                    $oldValue = $existingReports[$i]['value'];

                    // If we have a numeric value, we check if it's in a threshold sum
                    if (in_array($oldKey, $thresholds_values) == TRUE) {
                        if (is_numeric($oldValue) == TRUE) {
                            $thresholds_sum += intval($oldValue);
                        }
                    }
                }
            }

			if (count($thresholds_values) > 0 && $thresholds_max > 0 && $thresholds_sum >= $thresholds_max) {
				// Prepare the threshold alert message for the sender

				$Response = sprintf(ConfigGetString($bdd, 'server_alert_threshold'),
					$thresholds_max, $disease_name, $thresholds_sum, $periodLabel, date("d/m/Y", $startPeriod)
				);

				// Add the android keyword if defined
				$Response = $android_keyword_threshold . $Response;
				// Send alert message
				SendSMS($bdd, $contactDetails['PhoneNumber'], $Response, $serverId, $logFileName);

				return true;
			}
		}

		return false ;
	}
?>