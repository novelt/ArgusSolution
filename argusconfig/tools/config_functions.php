<?php

	// We need the default settings definition
	require_once(__DIR__."\..\config\defaults.php");

	// Get an integer configuration value
	function ConfigGetInteger ($bdd, $Key) {
		global $config_defaults;
		$SQL="SELECT valueInteger FROM ses_nvc WHERE collection='CONFIG' and `key`='".$bdd->escape($Key)."';";
		$Value=db_Scalar ($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		if ($Value===FALSE) {
			// Not yet a value
			if (isset($config_defaults[$Key])) {
				// Get the default value
				$Value=$config_defaults[$Key]['DefaultInteger'];
				// Save defaults values for integer and string
				ConfigSetInteger ($bdd, $Key, $config_defaults[$Key]['DefaultInteger']);
				ConfigSetString  ($bdd, $Key, $config_defaults[$Key]['DefaultString']);
			} else {
				// Error, no defaults found!
				LogErrorAndDie(sprintf(_("No integer default value found for key [%s]!"), $Key),__FUNCTION__,__LINE__,__FILE__);
			}
		} else {
			// Return the saved value
			$Value=intval($Value);
		}
		return($Value);
	}

	// Set a integer value to a configuration key
	function ConfigSetInteger ($bdd, $Key, $Value) {
		if (db_RowsCount ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_nvc","collection='CONFIG' and `key`='".$bdd->escape($Key)."'")>0) {
			// Update
			$List=array("valueInteger"=>intval($Value));
			db_Update ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_nvc",$List,"collection='CONFIG' and `key`='".$bdd->escape($Key)."'");
		} else {
			// Insert
			$List=array("collection"=>"'CONFIG'", "`key`"=>"'".$bdd->escape($Key)."'", "valueInteger"=>intval($Value), "valueString"=>"''");
			db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_nvc",$List);
		}
	}

	// Get a string configuration value
	function ConfigGetString ($bdd, $Key) {
		global $config_defaults;
		$SQL="SELECT valueString FROM ses_nvc WHERE collection='CONFIG' and `key`='".$bdd->escape($Key)."';";
		$Value=db_Scalar ($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		if ($Value===FALSE) {
			// Not yet a value
			if (isset($config_defaults[$Key])) {
				// Get the default value
				$Value=$config_defaults[$Key]['DefaultString'];
				// Save defaults values for integer and string
				ConfigSetInteger ($bdd, $Key, $config_defaults[$Key]['DefaultInteger']);
				ConfigSetString  ($bdd, $Key, $config_defaults[$Key]['DefaultString']);
			} else {
				// Error, no defaults found!
				LogErrorAndDie(sprintf(_("No string default value found for key [%s]!"), $Key),__FUNCTION__,__LINE__,__FILE__);
			}
		} else {
			// Return the saved value
			$Value=trim($Value);
		}
		return($Value);
	}
	
	// Set a string value to a configuration key
	function ConfigSetString ($bdd, $Key, $Value) {
		if (db_RowsCount ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_nvc","collection='CONFIG' and `key`='".$bdd->escape($Key)."'")>0) {
			// Update
			$List=array("valueString"=>"'".$bdd->escape(trim($Value))."'");
			db_Update ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_nvc",$List,"collection='CONFIG' and `key`='".$bdd->escape($Key)."'");
		} else {
			// Insert
			$List=array("collection"=>"'CONFIG'", "`key`"=>"'".$bdd->escape($Key)."'", "valueInteger"=>0, "valueString"=>"'".$bdd->escape(trim($Value))."'");
			db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_nvc",$List);
		}
	}

	// Get an NVC integer value, return a specified value if not found
	function NvcGetInteger ($bdd, $Collection, $Key, $NotFound) {
		global $config_defaults;
		$SQL="SELECT valueInteger FROM ses_nvc WHERE collection='".$bdd->escape($Collection)."' and `key`='".$bdd->escape($Key)."';";
		$Value=db_Scalar ($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		if ($Value===FALSE) {
			// Not found
			$Value=intval($NotFound);
		} else {
			// Return the saved value
			$Value=intval($Value);
		}
		return($Value);
	}
	
	// Get an NVC string value, return a specified value if not found
	function NvcGetString ($bdd, $Collection, $Key, $NotFound) {
		global $config_defaults;
		$SQL="SELECT valueString FROM ses_nvc WHERE collection='".$bdd->escape($Collection)."' and `key`='".$bdd->escape($Key)."';";
		$Value=db_Scalar ($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		if ($Value===FALSE) {
			// Not found
			$Value=strval($NotFound);
		} else {
			// Return the saved value
			$Value=strval($Value);
		}
		return($Value);
	}
	
	// Update or insert an NVC value
	function NvcSet ($bdd, $Collection, $Key, $Integer, $String) {
		if (db_RowsCount ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_nvc","collection='".$bdd->escape($Collection)."' and `key`='".$bdd->escape($Key)."'")>0) {
			// Update
			$List=array("valueInteger"=>intval($Integer), "valueString"=>"'".$bdd->escape(trim($String))."'");
			db_Update ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_nvc",$List,"collection='".$bdd->escape($Collection)."' and `key`='".$bdd->escape($Key)."'");
		} else {
			// Insert
			$List=array("collection"=>"'".$bdd->escape($Collection)."'", "`key`"=>"'".$bdd->escape($Key)."'", "valueInteger"=>intval($Integer), "valueString"=>"'".$bdd->escape(trim($String))."'");
			db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_nvc",$List);
		}
	}

	/******************************************************************************************************/
	/****************************** ARGUS GATEWAY DEVICE FUNCTIONS ****************************************/
	/******************************************************************************************************/
    /**
	 * Return all connected devices
	 *
     * @param $bdd
     * @param $phonePrefix
     *
     * @return bool
     */
	function getAllDevices($bdd, $phonePrefix)
	{
		$SQL="SELECT d.*, (SELECT count(*) FROM  ses_gateway_queue q WHERE q.sent IS NULL and q.gatewayId = d.gatewayId) AS pendingMessages FROM ses_gateway_devices d ";
        $SQL .= " WHERE d.gatewayId LIKE '" . $phonePrefix . "%' ;";

		$devices = db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		return $devices ;
	}

    /**
	 * Get the latest schema version installed
	 *
     * @param $bdd
     * @return bool$
     */
	function getLastSchemaVersion($bdd)
	{
		$SQL="SELECT version FROM ses_version ORDER BY id DESC LIMIT 1 ;";
		$version=db_Scalar($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		return($version);
	}

    /**
	 * Insert schema version installed
	 *
     * @param $bdd
     * @param $version
     * @return bool
     */
	function setLastSchemaVersion($bdd, $version)
	{
		$Values=array(
			"version" 	=> "'".$version."'",
			"installationDate"    	=> "'". date("Y-m-d H:i:s") ."'",
		);

		return db_Insert ($bdd,__FUNCTION__,__LINE__,__FILE__,"ses_version", $Values);
	}

?>