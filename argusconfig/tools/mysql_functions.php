<?php

	// Opening the MySQL connection
	function db_Open($Function,$Line,$File)
	{
		global $config;
		$bdd=new BDD();
		$bdd->server_hostname = $config["mysql_server"];
		$bdd->server_port = $config["mysql_port"];
		$bdd->server_db       = $config["mysql_db"];
		$bdd->server_login    = $config["mysql_user"];
		$bdd->server_password = $config["mysql_password"];
		if ($bdd->open()===FALSE)
		{
			// Erreur connexion SQL
			LogErrorAndDie(_("ERROR MySQL: Can't connect to database").' ('.$bdd->last_message.')',$Function,$Line,$File);
		}

		return($bdd);
	}

	// Closing the MySQL connection
	function db_Close($bdd)
	{
		$bdd->close();
	}

	// Simple SQL query without returning data
	function db_Query($bdd, $SQL, $Function, $Line, $File, $die = true)
	{
		if ($bdd->query($SQL)===FALSE) {
			// Erreur SQL
			LogErrorAndDie(_("ERROR MySQL: Simple query").' ('.$bdd->last_message.')'."\r\n".$SQL,$Function,$Line,$File, $die);

			return FALSE ;
		}

		return TRUE ;
	}
	
	// Get the last inserted id
	function db_LastInsertedId($bdd,$Function,$Line,$File)
	{
		return(intval(db_Scalar ($bdd,"SELECT LAST_INSERT_ID();",$Function,$Line,$File)));
	}

    /**
     * SQL query returning an array of data
     *
     * @param $bdd
     * @param $SQL
     * @param $Function
     * @param $Line
     * @param $File
     * @return array
     */
	function db_GetArray($bdd,$SQL,$Function,$Line,$File)
	{
		$rows=$bdd->getarray($SQL);
		if ($rows===FALSE) 
		{
			// Erreur SQL
			LogErrorAndDie(_("ERROR MySQL: Get array").' ('.$bdd->last_message.')'."\r\n".$SQL,$Function,$Line,$File);
		}
		return($rows);
	}

    /**
     * Start Transaction
     *
     * @param $bdd
     * @param $Function
     * @param $Line
     * @param $File
     *
     * @return bool
     */
	function db_StartTransaction($bdd,$Function,$Line,$File)
    {
        return db_Query($bdd,"START TRANSACTION", $Function, $Line, $File);
    }

    /**
     * Commit Transaction
     *
     * @param $bdd
     * @param $Function
     * @param $Line
     * @param $File
     *
     * @return bool
     */
    function db_CommitTransaction($bdd,$Function,$Line,$File)
    {
        return db_Query($bdd,"COMMIT", $Function, $Line, $File);
    }

    /**
     * Rollback Transaction
     *
     * @param $bdd
     * @param $Function
     * @param $Line
     * @param $File
     *
     * @return bool
     */
    function db_RollbackTransaction($bdd,$Function,$Line,$File)
    {
        return db_Query($bdd,"ROLLBACK", $Function, $Line, $File);
    }
	
	// Create lists of parameters and values for UPDATE/INSERT
	function db_GetUpdateList($List) {
		$StringList="";
		foreach ($List as $Parameter=>$Value) {
			if ($StringList!="") $StringList.=",";
			$StringList.=$Parameter."=".$Value;
		}
		return($StringList);
	}
	function db_GetInsertParametersList($List) {
		$StringList="";
		foreach ($List as $Parameter=>$Value) {
			if ($StringList!="") $StringList.=",";
			$StringList.=$Parameter;
		}
		return($StringList);
	}
	function db_GetInsertValueList($List) {
		$StringList="";
		foreach ($List as $Parameter=>$Value) {
			if ($StringList!="") $StringList.=",";
			$StringList.=$Value;
		}
		return($StringList);
	}

	// Get a scalar result from a query or FALSE if no response
	function db_Scalar ($bdd,$SQL,$Function,$Line,$File) {
		$result=FALSE;
		$rows=db_GetArray($bdd,$SQL,$Function,$Line,$File);
		if (count($rows)>0) {
			foreach ($rows[0] as $name=>$value) {
				if ($result===FALSE) {
					$result=$value;
				}
			}
		}
		return($result);
	}
	
	// Get a number of rows
	function db_RowsCount ($bdd,$Function,$Line,$File,$Table,$Condition="") {
		$SQL="SELECT COUNT(*) FROM ".$Table;
		if ($Condition!="") $SQL.=" WHERE ".$Condition;
		return(db_Scalar ($bdd,$SQL,$Function,$Line,$File));
	}
	
	// Insert a row
	function db_Insert ($bdd,$Function,$Line,$File,$Table,$List, $die=false) {
		$SQL="INSERT INTO ".$Table." (".db_GetInsertParametersList($List).") VALUES (".db_GetInsertValueList($List).");";
		//echo($SQL);
		return(db_Query($bdd,$SQL,$Function,$Line,$File,$die));
	}

	// Delete a row
	function db_Delete ($bdd,$Function,$Line,$File,$Table,$Condition, $die=false) {
        $SQL="DELETE FROM ".$Table." WHERE ".$Condition;
        //echo($SQL);
        return(db_Query($bdd,$SQL,$Function,$Line,$File,$die));
    }
	
	// Update a row
	function db_Update ($bdd,$Function,$Line,$File,$Table,$List,$Condition) {
		$SQL="UPDATE ".$Table." SET ".db_GetUpdateList($List)." WHERE ".$Condition.";";
		return(db_Query($bdd,$SQL,$Function,$Line,$File));
	}

	/**
	 * Lock Tables $tables
	 *
	 * @param $bdd
	 * @param $Function
	 * @param $Line
	 * @param $File
	 * @param $tables
	 * @param $type
	 * @return bool
	 */
	function db_Lock ($bdd,$Function,$Line,$File, $tables, $type)
    {
		$list_tables = ''; // List of tables to lock

		// verification du paramètre $type;
		$type = strtoupper($type);
		if (!in_array($type, array('WRITE', 'READ'))) {
			LogErrorAndDie(_("ERROR Parameter type can be WRITE or READ"),$Function,$Line,$File);
			return FALSE;
		}
		if (is_string ($tables) ) {
			$list_tables = $tables . ' ' . $type;
		} elseif (is_array ($tables) ) {
			foreach ($tables as $key => $value) {
				if ( !is_string ($value) ) {
					LogErrorAndDie(_("ERROR with Parameter tables"),$Function,$Line,$File);
					return FALSE ;
				}
				if ( !empty ($list_tables) ) {
					$list_tables .= ', ';
				}
				$list_tables .= $value .' ';
				if (is_string ($key) ) {
					$list_tables .= 'AS ' . $key . ' ';
				}
				$list_tables .= $type;
			}
		} else {
			LogErrorAndDie(_("ERROR with Parameter tables"),$Function,$Line,$File);
			return FALSE;
		}
		$SQL = 'LOCK TABLE ' . $list_tables . ';';

		return db_Query($bdd, $SQL, $Function, $Line, $File);
	}

	/**
	 * Unlock all tables
	 *
	 * @param $bdd
	 * @param $Function
	 * @param $Line
	 * @param $File
	 * @return bool
	 */
	function db_Unlock ($bdd,$Function,$Line,$File)
    {
		$SQL = 'UNLOCK TABLES;';
		return db_Query($bdd, $SQL, $Function,$Line,$File);
	}

	// -------------------------------------------------------------------

    /**
     * Class BDD : Classe de gestion des accès aux bases de données nécessaires
     */
	class BDD 
	{
		// Pseudo-constantes
		var $connection_timeout=5;
		public $server_hostname='';
		public $server_port='';
		public $server_login='';
		public $server_password='';
		public $server_db='';
		
		// Variables globales
		var $link;
		var $last_message='';
		var $info='';
		var $connected=false;
	
		// Constructeur
		function __construct()
		{
		}
		
		// Ouverture connexion
		function open()
		{
			$this->link = @mysqli_init();
			if (!$this->link)
			{
				$this->last_message='Error_BDD_open : mysqli_init failed!';
				return(false);
			}
			if (!@mysqli_options($this->link, MYSQLI_OPT_CONNECT_TIMEOUT, $this->connection_timeout)) 
			{
				$this->last_message='Error_BDD_open : mysqli_options for MYSQLI_OPT_CONNECT_TIMEOUT failed!';
				return(false);
			}
			if (!@mysqli_real_connect($this->link, $this->server_hostname, $this->server_login, $this->server_password, $this->server_db, $this->server_port))
			{
				$this->last_message='Error_BDD_open : mysqli_real_connect error '.mysqli_connect_errno().' ('.mysqli_connect_error().')';
				return(false);			
			}
			$this->connected=true;
			if (!@mysqli_set_charset($this->link, "utf8")) 
			{
				$this->last_message='Error_BDD_open : mysqli_set_charset error ('.mysqli_error($this->link).')';
				$this->close();
				return(false);			
			}

			// TimeZone regarding $config["timezone"] initialized in common.php
			/*$now = new DateTime();
			$mins = $now->getOffset() / 60;
			$sgn = ($mins < 0 ? -1 : 1);
			$mins = abs($mins);
			$hrs = floor($mins / 60);
			$mins -= $hrs * 60;
			$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
			$this->link->query("SET time_zone='$offset';");*/

			$this->info='Librairie='.mysqli_get_client_version().' Connexion='.@mysqli_get_host_info($this->link).' Server='.@mysqli_get_server_info($this->link);
			$this->last_message='OK_BDD_open';
			return(true);
		}
		
		// Fermeture connexion
		function close()
		{
			if ($this->connected==true)
			{	
				@mysqli_close($this->link);
				$this->connected=false;
			}
			return(true);
		}

		// Requête simple
		function query($query_string)
		{
			if ($this->connected==true)
			{
				if(@mysqli_real_query($this->link,$query_string)==true)
				{
					$this->last_message=@mysqli_info($this->link);
					return(true);
				}
				else
				{
					// echo("ERROR_MYSQL_".@mysqli_sqlstate($this->link)." ");
					$this->last_message="SQLSTATE=".@mysqli_sqlstate($this->link).", ERRNO=".@mysqli_errno($this->link).", ".@mysqli_error($this->link);
					return(false);
				}
			}
			else
				return(false); 
		}
		
		// Requête avec résultats
		// Attention, tous les champs sont retournés sous forme de chaine ou NULL
		// ["Null"]   => NULL
		// ["Float"]  => string(4)  "1.23"
		// ["Entier"] => string(1)  "1"
		// ["Chaine"] => string(7)  "CHA'INE"
		// ["Date"]   => string(19) "2011-09-20 21:23:47"
		function getarray($query_string)
		{
			if ($this->connected==true)
			{
				if ($result=mysqli_query($this->link, $query_string))
				{
					$this->last_message=@mysqli_info($this->link);
					$rows=array();
					while ($row = mysqli_fetch_assoc($result)) 
					{
						$rows[]=$row;
					}
					@mysqli_free_result($result);
					return($rows);
				}
				else
				{
					// echo("ERROR_MYSQL_".@mysqli_sqlstate($this->link)." ");
					$this->last_message="SQLSTATE=".@mysqli_sqlstate($this->link).", ERRNO=".@mysqli_errno($this->link).", ".@mysqli_error($this->link);
					return(false);
				}
			}
			else
				return(false); 
		}
		
		// Dernier ID inséré 
		// Attention, probleme avec BIGINT
		function inserted_id()
		{
			if ($this->connected==true)
				return(@mysqli_insert_id($this->link));
			else
				return(false);
		}
	
		// Echappement chaines SQL - Transforme tout type de variable en chaine et le NULL en chaine vide
		function escape($string)
		{
			if ($this->connected==true)
				return(@mysqli_real_escape_string($this->link,$string));
			else
				return(false);
		}
		
		// Escape Quoted : Formattage sous forme de chaine de différents types avec gestion des NULL
		function eq($valeur,$type='')
		{
			// Valeur NULL
			if (is_null($valeur))
				return ('NULL');
			// Boolean
			elseif ($type=='bool')
				if ($valeur===TRUE)
					return('1');
				else
					return('0');
			// Date/Time (depuis timestamp)
			elseif ($type=='time')
				return(date("Y-m-d H:i:s",intval($valeur)));
			// Entier
			elseif ($type=='int')
				return(strval(intval($valeur)));
			// Virgule flottante
			elseif ($type=='float')
				return(number_format(floatval($valeur),10,'.',''));
			// Le reste et les chaines
			else
				return("'".$this->escape($valeur)."'");
		}
		
		// Récupération d'un timestamp ou null à partir d'une chaine
		function ts($time)
		{
			if (is_null($time))
				return(null);
			else
				return(strtotime($time));
		}
		
		// Formattage DateHeure FR
		function DateHeure($data)
		{
			return(date("d/m/Y H:i:s",strtotime($data)));
		}
		
		// Formattage Date FR seulement
		function Date($data)
		{
			return(date("d/m/Y",strtotime($data)));
		}
		
		// Desctructeur
		function __destruct()
		{
			$this->close();
		}
   }

?>