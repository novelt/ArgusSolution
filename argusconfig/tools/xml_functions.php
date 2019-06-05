<?php

	// Load and validate an XML file with a schema
	// Return an array of errors (and no error if everything was fine)
	function xml_validate($XmlFile, $XsdSchema) {
		$errors_list=array();
		// Try to load the XML document
		libxml_clear_errors();
		libxml_use_internal_errors(TRUE);
		$DOM=new DOMDocument();
		if (@$DOM->load($XmlFile)===FALSE) {
			// Loading error
			$xml_errors=xml_get_errors();
			$errors_list[]="XML loading failed with ".count($xml_errors)." error(s):";
			for ($i=0; $i<count($xml_errors); $i++) {
				$errors_list[]=$xml_errors[$i];
			}
		} else {
			// Try to validate the XML document with the schema
			libxml_clear_errors();
			libxml_use_internal_errors(TRUE);	
			if (@$DOM->schemaValidate($XsdSchema)===FALSE) {
				// Validating error
				$xml_errors=xml_get_errors();
				$errors_list[]="XML validation failed with ".count($xml_errors)." error(s):";
				for ($i=0; $i<count($xml_errors); $i++) {
					$errors_list[]=$xml_errors[$i];
				}
			}
		}
		return($errors_list);
	}

	// Get XML errors formatted in an array
	function xml_get_errors() {
		$errors_list=array();
		foreach (libxml_get_errors() as $error) {
			$message="";
			switch($error->level) {
				case LIBXML_ERR_WARNING:
					$message.="WARNING!";
					break;
				case LIBXML_ERR_ERROR:
					$message.="ERROR!";
					break;
				case LIBXML_ERR_FATAL:
					$message.="FATAL!";
					break;
			}
			$message.=" Line=".$error->line.", Col=".$error->column;
			if (isset($error->file)) $message.=", File=[".$error->file."]";
			$message.=" - ".trim($error->message);
			$errors_list[]=$message;
		}
		return($errors_list);
	}

	// Return an array of nodes (childs) having the specified tag name
	function xml_find_childs_by_tag($node, $tag) {
		$filtered_nodes=array();
		foreach ($node->childNodes as $child) {
			if ($child->nodeName==$tag) {
				$filtered_nodes[]=$child;
			}
		}
		return($filtered_nodes);
	}
	
	// Get string value of a child node identified by its tag name
	// Return FALSE if no child is found
	function xml_get_child_value($node, $tag) {
		$value=FALSE;
		foreach ($node->childNodes as $child) {
			if ($child->nodeName==$tag) {
				$value=trim($child->nodeValue);
			}
		}
		return($value);
	}
	
	// Return an array of nodes (sub-childs) having the specified tag name from a child node
	function xml_find_sub_childs_by_tag($node, $child_tag, $sub_child_tag) {
		$sub_childs=array();
		$childs=xml_find_childs_by_tag($node, $child_tag);
		for ($i=0; $i<count($childs); $i++) {
			$temp_sub_childs=xml_find_childs_by_tag($childs[$i], $sub_child_tag);
			for ($j=0; $j<count($temp_sub_childs); $j++) {
				$sub_childs[]=$temp_sub_childs[$j];
			}
		}
		return($sub_childs);
	}


?>