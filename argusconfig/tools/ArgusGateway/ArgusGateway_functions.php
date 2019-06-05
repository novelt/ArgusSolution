<?php

	// Global variables
	//$argus_table_name = 'ses_gateway_queue';


	// Send a SMS message using the Queue of SMSSync
	function SendSMS ($bdd, $Recipient, $Message, $serverId, $logFileName)
    {
		global $config;
		require_once("./tools/logging_functions.php");
		require_once("./tools/config_functions.php");
		// Check if a message identifier must be sent back
		global $message_identifier_value, $message_identifier_receiver;

		if (isset($message_identifier_value) && isset($message_identifier_receiver) && $Recipient==$message_identifier_receiver) {
			// We need to add the message identifier
			$Message=$message_identifier_value.$Message;
		}

		// Get the maximum SMS size
		$MaxSize=ConfigGetInteger ($bdd, 'max_length_sms_sent');
		// Check the message size
		if (strlen($Message)>$MaxSize && $MaxSize>10) {
			// Truncate and add an ellipsis
			$Message=substr($Message, 0, $MaxSize-3)."...";
		}

		// Store SMS answers in DataBase
		$Status=insert_waiting_SMS($bdd, $Recipient, $Message, $serverId);

		// Log message
		if ($Status==FALSE) {
			// Error
			LogMessage($logFileName.'-errors', sprintf(_("ERROR while creating SMS in the ses_gateway_queue! Number=[%s] Message=[%s]"),$Recipient,$Message));
		} else {
			// OK
			LogMessage($logFileName, sprintf(_("<= SMS created in ses_gateway_queue: Number=[%s] Message=[%s]"),$Recipient,$Message));
		}
		// Return status
		return($Status);
	}

	function get_waiting_SMS($bdd, $serverId, $pending)
	{
		global $config;

		$pending_condition = "1=1";
		if (!$pending) {
			$pending_condition = "(pending IS NULL OR pending = 0)";
		}
		else {
			$pending_condition = "(pending = 1) AND TIMESTAMPDIFF(MINUTE, updateDate, now()) > 15 ";
		}

		// Retrieve SMS to be sent
		$SQL="
			SELECT
				id, phoneNumber, message
			FROM
				ses_gateway_queue
			WHERE
				sent IS NULL
			AND
			 	".$pending_condition."
			AND
				gatewayId = '".$serverId."'
			AND
				failure <= ".$config["ArgusSMS_errorLimit"]."
			ORDER BY
				creationDate ASC
			  LIMIT
			  	".$config["ArgusSMS_pendingSms"]."
		;";

		$reply = array() ;
		$sms_sync_queue=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

		foreach($sms_sync_queue as $sms)
		{
			$message = new ArgusSMS_OutgoingMessage();
			$message->id = $sms["id"];
			$message->to =  $sms["phoneNumber"];
			$message->message = $sms["message"];
			$reply[] = $message;
		}

		return $reply ;
	}

	function set_pending_SMS($bdd, $messages)
	{
		$List = array(
			"pending" => 'TRUE',
			"updateDate" => 'now()'
		);

		foreach($messages as $message)
		{
			db_Update($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_gateway_queue",$List, "id = ".$message->id);
		}
	}

	function insert_waiting_SMS($bdd, $recipient, $message, $serverId)
	{
		$Values=array(
			"phoneNumber" 	=> "'".$recipient."'",
			"message"    	=> "'".$bdd->escape($message)."'",
			"gatewayId" 	=> "'".$serverId."'",
            "creationDate"  => "'". date("Y-m-d H:i:s") ."'",
            "creationDay"  => "'". date("Y-m-d") ."'",
		);

		return db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_gateway_queue", $Values);
	}

	function insert_incoming_SMS($bdd, $recipient, $message, $serverId)
	{
		$Values=array(
			"phoneNumber" 	=> "'".$recipient."'",
			"message"    	=> "'".$bdd->escape($message)."'",
			"gatewayId" 	=> "'".$serverId."'",
            "creationDate"  => "'". date("Y-m-d H:i:s") ."'",
            "creationDay"  => "'". date("Y-m-d") ."'",
		);

		db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_incoming_sms", $Values);

        return db_LastInsertedId($bdd, __FUNCTION__, __LINE__, __FILE__);
	}

    /**
     * Update incoming
     *
     * @param $bdd
     * @param $smsId
     * @param $status
     * @param $type
     * @param $information
     */
	function update_incoming_SMS($bdd, $smsId, $status, $type, $information)
    {
        $List = array(
            "status" => $status,
            "`type`" => $type,
            "comments" => "'".$information."'",
            "updateDate" => "'". date("Y-m-d H:i:s") ."'"
        );

        db_Update($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_incoming_sms",$List, "id = ".$smsId);
    }

	function update_result_Failed($bdd, $id)
	{
		$List = array(
			"pending" => 'FALSE',
			"updateDate" => 'now()',
			"failure" => 'failure + 1'
		);

		db_Update($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_gateway_queue",$List, "id = ".$id);
	}

	function update_result_Sent($bdd, $id)
	{
		$List = array(
			"pending" => 'FALSE',
			"updateDate" => 'now()',
			"sent" => 'now()'
		);

		db_Update($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_gateway_queue",$List, "id = ".$id);
	}

	function update_gateway_device($bdd, $request)
	{
        // we want NULL if poll interval is not send by the gateway, not 0
        $pollInterval = isset($request->pollInterval) ? "'$request->pollInterval'" : "NULL";

        $SQL=" INSERT INTO ses_gateway_devices (gatewayId, operator, manufacturer, model, sdk, versionName, version, battery, power, pollInterval, updateDate)
 			   VALUES ('".$request->phone_number."', 
 			            '".$request->phone_operator."', 
 			            '".$request->manufacturer."', 
 			            '".$request->model."', 
 			            '".$request->sdk_int."', 
 			            '".$request->version_name."', 
 			            '".$request->version."', 
 			            '".$request->battery."', 
 			            '".$request->power."', 
 			             $pollInterval, 
 			            '". date("Y-m-d H:i:s") ."')
 			   ON DUPLICATE KEY UPDATE  operator = '".$request->phone_operator."',
 			   							manufacturer='".$request->manufacturer."',
 			    						model = '".$request->model."',
 			    						sdk = '".$request->sdk_int."',
 			    						versionName = '".$request->version_name."',
 			    						version = '".$request->version."',
 			    						battery = '".$request->battery."',
 			    						power = '".$request->power."',
 			    						pollInterval = $pollInterval, 
 			    						updateDate= '". date("Y-m-d H:i:s") ."'
 			    						;";

		db_Query($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);

	}

    /**
     * Check if the sender is not a known gateway number.
     *
     * @param $bdd
     * @param $number
     * @return bool
     */
	function is_a_gatewayNumber($bdd, $number)
    {
        return db_RowsCount($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_gateway_devices","gatewayId = '".$number."'");
    }
?>