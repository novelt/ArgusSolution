<?php

	// Add multiple test results if a test can provide more than one result
	function add_multiple_tests($list, $results) {
		$new_list=$list;
		for ($i=0; $i<count($results); $i++) {
			$new_list[]=$results[$i];
		}
		return($new_list);
	}

	// Check if a directory exists
	function test_directory_exists($Path) {
		$Test=array('Test'=>_("Directory exists"),'Information'=>_("Directory").'='.$Path,'OK'=>FALSE);
		if (file_exists($Path) && is_dir($Path)) {
			$Test['OK']=TRUE;
		}
		return($Test);
	}

	// Check if a file exists
	function test_file_exists($Path) {
		$Test=array('Test'=>_("File exists"),'Information'=>_("File").'='.$Path,'OK'=>FALSE);
		if (file_exists($Path) && is_file($Path)) {
			$Test['OK']=TRUE;
		}
		return($Test);
	}
	
	// Check if files content are identical
	function test_files_content($Path1, $Path2) {
		$Test=array('Test'=>_("Files content"),'Information'=>_("File").'='.$Path1.', '._("File").'='.$Path2,'OK'=>FALSE);
		if (file_exists($Path1) && is_file($Path1) && file_exists($Path2) && is_file($Path2) && sha1_file($Path1)==sha1_file($Path2)) {
			$Test['OK']=TRUE;
		}
		return($Test);
	}
	
	// Check application path
	/*function test_application_path() {
		global $config;
		$RootPath=strtolower("C:\\xampp\\htdocs\\ses\\");
		$Test=array('Test'=>_("Application root path"),'Information'=>_("Expected").'='.$RootPath.', '._("Actual").'='.strtolower($config["path_base"]),'OK'=>FALSE);
		if (strtolower($config["path_base"])==$RootPath) {
			$Test['OK']=TRUE;
		}
		return($Test);
	}*/

	// Check application URL
	/*function test_application_url() {
		$RootUrl="/ses/";
		$Test=array('Test'=>_("Application root URL"),'Information'=>_("Expected").'='.$RootUrl.'*, '._("Actual").'='.$_SERVER['PHP_SELF'],'OK'=>FALSE);
		if (strpos($_SERVER['PHP_SELF'],$RootUrl)===0) {
			$Test['OK']=TRUE;
		}
		return($Test);
	}*/

	// Check MySQL connection
	function test_mysql_connection() {
		global $config;
		$Test=array('Test'=>_("MySQL connection"),'Information'=>_("Login").'='.$config["mysql_user"].'@'.$config["mysql_server"],'OK'=>FALSE);
		$mysqli_connection = @new MySQLi($config["mysql_server"],$config["mysql_user"],$config["mysql_password"],'mysql');
		if(!$mysqli_connection->connect_error) {
			$mysqli_connection->close();
			$Test['OK']=TRUE;
		}
		return($Test);
	}
	
	// Check MySQL schema
	function test_mysql_schema() {
		global $config;
		$Test=array('Test'=>_("MySQL schema exists"),'Information'=>_("Schema").'='.$config["mysql_db"],'OK'=> test_mysql_schema_exist());
		return($Test);
	}

    /**
	 * Check schema version on ses_version table
	 *
     * @param $bdd
     * @return array
     */
	function test_schema_version($bdd) {
		global $config;
		$SQL="SELECT version FROM ses_version ORDER BY id DESC LIMIT 1 ;";
		$version=db_Scalar($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		$Test=array('Test'=>_("MySQL schema version"),'Information'=>_("Expected").'='.$config["schema_version"].', '._("Actual").'='.$version,'OK'=>FALSE);
		if ($version==$config["schema_version"]) {
			$Test['OK']=TRUE;
		}
		return($Test);
	}

	// Launch tests for diagnosis
	function run_diagnosis() {
		global $config;
		// Default values
		$return=array();
		$return['nb_tests']=0;
		$return['nb_errors']=0;
		$return['tests']=array();
		// Tests - Directory
		$return['tests'][]=test_directory_exists($config["path_base"]);
		$return['tests'][]=test_directory_exists($config["path_data_input"]);
		$return['tests'][]=test_directory_exists($config["path_data_ouput"]);
		$return['tests'][]=test_directory_exists($config["path_data_processed"]);
		$return['tests'][]=test_directory_exists($config["path_logs"]);
		$return['tests'][]=test_directory_exists($config["path_locale"]);
		$return['tests'][]=test_directory_exists($config["path_ressources"]);
		$return['tests'][]=test_directory_exists($config["path_backup"]);
		// Tests - File
		$return['tests'][]=test_file_exists($config["php_conf_path"]);
		$return['tests'][]=test_file_exists($config["mysql_conf_path"]);
		$return['tests'][]=test_file_exists($config["apache_conf_path"]);
		$return['tests'][]=test_file_exists($config["mysql_exe_path"]);
		$return['tests'][]=test_file_exists($config["mysql_dump_exe_path"]);
		$return['tests'][]=test_file_exists($config["xampp_version_path"]);
		// Tests - Files content
		$return['tests'][]=test_files_content($config["php_conf_path"],            $config["path_ressources"].'config_php.txt');
		$return['tests'][]=test_files_content($config["mysql_conf_path"],       $config["path_ressources"].'config_mysql.txt');
		$return['tests'][]=test_files_content($config["apache_conf_path"], $config["path_ressources"].'config_apache.txt');
		$return['tests'][]=test_files_content($config["xampp_version_path"],  $config["path_ressources"].'config_xampp.txt');

		// Application path/url
		//$return['tests'][]=test_application_path();
		//$return['tests'][]=test_application_url();

		// Check database connection
		$test_mysql_connection=test_mysql_connection();
		$return['tests'][]=$test_mysql_connection;
		if ($test_mysql_connection['OK']==TRUE) {
			// Check database schema
			$test_mysql_schema=test_mysql_schema();
			$return['tests'][]=$test_mysql_schema;
			if ($test_mysql_connection['OK']==TRUE) {
				// Using normal MySQL tool
				require_once("mysql_functions.php");
				require_once("config_functions.php");
				require_once("string_functions.php");
				$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
				// Database is avalaible, we can do the tests needing MySQL
				$return['tests'][]=test_schema_version($bdd);
				//$return['tests'][]=test_server_phone_number($bdd);
				// Closing connection
				db_Close($bdd);
			}
		}
		// Return tests results
		$return['nb_tests']=count($return['tests']);
		$nb_errors=0;
		for ($i=0; $i<count($return['tests']); $i++) {
			if ($return['tests'][$i]['OK']==FALSE) {
				$nb_errors++;
			}
		}
		$return['nb_errors']=$nb_errors;
		return($return);
	}

	/**
	 * Test if argus gateways are still alive
	 *
	 * @return array
	 */
	function run_diagnosis_argus_gateway($phonePrefix = '') {
		global $config;
		// Default values
		$return=array();
		$return['nb_tests']=0;
		$return['nb_errors']=0;
		$return['tests']=array();

		// Using normal MySQL tool
		require_once("mysql_functions.php");
		require_once("config_functions.php");
		$bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);

		$devices = getAllDevices($bdd, $phonePrefix);
		// Test Argus Gateway devices
		foreach ($devices as $device) {

			$test = array();
			$test['OK'] = TRUE;

			$test['Test'] = 'Is this gateway device alive';
			$test['Device Id'] = $device['gatewayId'];
			$test['Operator'] = $device['operator'];
			$test['Manufacturer'] = $device['manufacturer'];
			$test['Model'] = $device['model'];
			$test['Version'] = $device['versionName'];
			$test['Battery'] = $device['battery'].' %';
            $test['Pending Messages'] = $device['pendingMessages'];
            $test['Poll Interval'] = isset($device['pollInterval']) ? $device['pollInterval'] .' sec' : 'Unknown';
			$test['Last Update'] = $device['updateDate'];

			$updateDate = strtotime($device['updateDate']) ;
			$now = strtotime('now');
			$dateDiff = $now - $updateDate ;
			$minutes = round($dateDiff / 60, 1);

			$battery = intval($device['battery']);
			$power = intval($device['power']);
            $pendingMessages = intval($device['pendingMessages']);
            $pollInterval = isset($device['pollInterval']) ? intval($device['pollInterval']) : null ;

			switch($power){
				case 0:
					$test['Power'] = 'Battery';
					break;
				case 1:
					$test['Power'] = 'AC';
					break;
				case 2:
					$test['Power'] = 'USB';
					break;
				default:
					$test['Power'] = 'Unknown';
			}

			if ($minutes > $config["Gateway_alive_minutes"]){
				$test['Information'] = 'This device is not responding';
				$test['OK'] = FALSE ;
				$return['nb_errors']++;
			}
			else if ($battery <= 15 && $power == 0) {
				$test['Information'] = 'This device need to be plugged to a source of electricity';
				$test['OK'] = FALSE ;
				$return['nb_errors']++;
			} else if ($pendingMessages > $config["Gateway_max_pending_sms"]) {
                $test['Information'] = 'This device has too much non sent messages';
                $test['OK'] = FALSE ;
                $return['nb_errors']++;
            } else if ($pollInterval === 0) {
                $test['Information'] = 'This device has poll interval set to never';
                $test['OK'] = FALSE ;
                $return['nb_errors']++;
            }
            else {
				$test['Information'] = 'This device is working well';
			}

			$return['tests'][] = $test;
			$return['nb_tests'] ++ ;
		}

		// Closing connection
		db_Close($bdd);

		return $return ;
	}

	/**
	 * Run and sum up All diagnosis
	 */
	function run_all_diagnosis()
    {
		$d1 = run_diagnosis();
		$d3 = run_diagnosis_argus_gateway();

		$return = array();
		$return['nb_tests'] = $d1['nb_tests'] + $d3['nb_tests'];
		$return['nb_errors'] = $d1['nb_errors'] + $d3['nb_errors'];

		return $return;
	}

	function test_mysql_schema_exist(){
		global $config;
		$result = FALSE ;
		$mysqli_connection = @new MySQLi($config["mysql_server"],$config["mysql_user"],$config["mysql_password"],'mysql');
		if(!$mysqli_connection->connect_error) {
			// Connected, list schemas
			if ($mysqli_result = $mysqli_connection->query("show databases;")) {
				while($row = $mysqli_result->fetch_row()) {
					if (strtolower($row[0])==strtolower($config["mysql_db"])) {
						$result = TRUE;
					}
				}
				$mysqli_result->close();
			}
			$mysqli_connection->close();
		}

		return $result;
	}
?>