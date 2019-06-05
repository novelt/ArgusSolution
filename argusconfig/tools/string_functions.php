<?php

	// Get site path from reference
	// Return empty value if no site found
	function GetSitePathFromReference($bdd, $Reference) {
		$path='';
		$SQL="SELECT path FROM frontline_group WHERE path LIKE '%/".$bdd->escape($Reference)."';";
		$result=db_GetArray($bdd,$SQL,__FUNCTION__,__LINE__,__FILE__);
		if (count($result)==1) {
			$path=$result[0]['path'];
		}
		return($path);
	}

	function GetParentPathFromPath($path){
		if (!isset($path) || $path==null){
			return null ;
		}
		$ref = GetSiteReferenceFromPath($path);
		if (!isset($ref) || $ref==null || $ref==''){
			return null;
		}

		$parent = substr($path, 0, - strlen($ref) - 1);

		return $parent ;
	}

	// Check if site exists
	function IsSiteExists($bdd, $Reference) {
		if (GetSitePathFromReference($bdd, $Reference)!='') {
			return(TRUE);
		} else {
			return(FALSE);
		}
	}

	// Get sites hierarchy from path
	// Empty array if SitesRoot or problem
	function GetSiteHierarchyFromPath ($Path) {
		$list=array();
		if (!isset($Path) || $Path==null) return($list);
		$Path=str_replace('/SitesRoot/','',$Path);
		$Path=str_replace('/SitesRoot','',$Path);
		$Path=trim($Path);
		if ($Path=='') return($list);
		$list=explode('/',$Path);
		return($list);
	}

	// Get site reference from path
	// Empty if SitesRoot
	function GetSiteReferenceFromPath ($Path) {
		$list=GetSiteHierarchyFromPath($Path);
		if (count($list)>0) {
			return($list[count($list)-1]);
		} else {
			return('');
		}
	}

	// Get site level from path
	// SitesRoot=0
	function GetSiteLevelFromPath ($Path) {
		$list=GetSiteHierarchyFromPath($Path);
		return(count($list));
	}
	
	// Check if string is set and not null and not empty
	function TestString($String, $ValueIfNull) {
		if (!isset($String) || $String==null || $String=='')
			return($ValueIfNull);
		else
			return($String);
	}

	// Replace accented characters by non-accented characters
	// Source : http://www.weirdog.com/blog/php/supprimer-les-accents-des-caracteres-accentues.html
	function RemoveAccents($str, $charset='utf-8') {
		$str = htmlentities($str, ENT_NOQUOTES, $charset);
		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
		$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caract�res
		return $str;
	}
	
	// Clean phone number
	// Remove all characters excepted +,0-9
	function CleanPhoneNumber ($Number) {
		$Cleaned="";
		for ($i=0; $i<strlen($Number); $i++) {
			$code=ord(substr($Number,$i,1));
			if (($code>=48 && $code<=57) || $code==43) {
				$Cleaned.=substr($Number,$i,1);
			}
		}
		return($Cleaned);
	}
	
	// Check phone number format
	// Must be +XXXX
	function CheckPhoneNumber ($Number) {
		if (strlen($Number)<5) {
			// Too short
			return(FALSE);
		} elseif (substr($Number,0,1)!="+") {
			// Must start by +
			return(FALSE);
		} else {
			// Check the numbers
			$OK=TRUE;
			for ($i=1; $i<strlen($Number); $i++) {
				$code=ord(substr($Number,$i,1));
				if ($code<48 || $code>57) {
					// Not a number
					$OK=FALSE;
				}
			}
			return($OK);
		}
	}
		
	// Create a clean keyword (alphanumeric in uppercase, no accents, no space)
	function CreateKeyword($String)	{
		// Remove accents
		$String=RemoveAccents($String);
		// Convert to uppercase
		$String=strtoupper($String);
		// Remove all non alphanumeric characters
		$FinalString="";
		for ($i=0; $i<strlen($String); $i++) {
			$code=ord(substr($String,$i,1));
			if (($code>=48 && $code<=57) || ($code>=65 && $code<=90)) {
				$FinalString.=substr($String,$i,1);
			}
		}
		return($FinalString);
	}
	
	// Get the primary keyword of a list
	function GetPrimaryKeyword($KeywordsList) {
		$Tokens=explode(",", $KeywordsList);
		return(trim(strtoupper($Tokens[0])));
	}

?>