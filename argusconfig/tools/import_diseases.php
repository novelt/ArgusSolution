<?php

	require_once("config_functions.php");
	require_once("mysql_functions.php");
	require_once("string_functions.php");

	// Create the keywords list
	// Reference as keyword if no keyword specified
	function CreateKeywordListFromXml($node, $reference)
    {
		// Final keyword list
		$List=array();
		$value = xml_get_child_value($node,"key_word");
		// Check keywords were specified
		if ($value === FALSE) {
			// No keyword specified, we use the reference as unique keyword
			$List[]=CreateKeyword($reference);
		} else {
            $List[]=CreateKeyword($value);
        }
		// Return the list of keywords
		return($List);
	}

    /**
     * Check diseases configuration
     *
     * @param $bdd
     * @param $MainNode
     * @param $diseases
     * @return array
     */
	function CheckDiseases($bdd, $MainNode, &$diseases) {
		global $config;

		// Error list
		$errors_list=array();
		
		// =======================================
		// Step #1 - Reading the diseases from XML
		// =======================================
		
		// Get the child nodes
		$disease_nodes=xml_find_childs_by_tag($MainNode, "disease");

		for ($i=0; $i<count($disease_nodes); $i++) {
			// New disease
			$disease=array();
			// First level values
			$disease['reference']=xml_get_child_value($disease_nodes[$i],"reference");
			$disease['name']=xml_get_child_value($disease_nodes[$i],"name");
			$disease['keywords']=CreateKeywordListFromXml($disease_nodes[$i], $disease['reference']);

			if ($disease['name']===FALSE) {
				$disease['name']=$disease['reference'];
			}
			// Get values
			$Error=FALSE;
			$disease['values']=array();
			$value_nodes=xml_find_sub_childs_by_tag($disease_nodes[$i], "values", "value");
			for ($j=0; $j<count($value_nodes); $j++) {
				// First level values
				$value=array();
				$value['reference']=xml_get_child_value($value_nodes[$j],"reference");
				$value['period']=xml_get_child_value($value_nodes[$j],"period");
				$value['position']=xml_get_child_value($value_nodes[$j],"position");
				$value['type']=xml_get_child_value($value_nodes[$j],"type");
				$value['mandatory']=xml_get_child_value($value_nodes[$j],"mandatory");
				$value['keywords']=CreateKeywordListFromXml($value_nodes[$j], $value['reference']);
				// Managing optional values
				if ($value['position']===FALSE) {
					$value['position']=1;
				} else {
					$value['position']=intval($value['position']);
				}
				if ($value['type']===FALSE || strtolower($value['type'])=="integer") {
					$value['type']="Integer";
				}
				/*
				 * Xsd schema already test values
				else {
					$value['type']="String";
				}
				*/
				if ($value['mandatory']===FALSE || strtolower($value['mandatory'])=="yes") {
					$value['mandatory']=1;
				} else {
					$value['mandatory']=0;
				}
				// Check reference validity
				if ($value['reference']===FALSE || trim($value['reference'])=="") {
					$errors_list[]=sprintf(_("ERROR ImportDiseases: A value with an empty reference was found for disease %s"),$disease['reference']);
					$Error=TRUE;
				} else {
					// Check if value already exists for the period
					if (isset($disease['values'][$value['reference'].'-'.$value['period']])) {
						// Value already exists for the period
						$errors_list[]=sprintf(_("ERROR ImportDiseases: The value %s for the period %s already exist for disease %s"),$value['reference'],$value['period'],$disease['reference']);
						$Error=TRUE;
					} else {
						// Ok, we keep the value
						$disease['values'][$value['reference'].'-'.$value['period']]=$value;
					}
				}
			}

			// Checking values
			if ($Error==FALSE && ($disease['reference']===FALSE || trim($disease['reference'])=="")) {
				// Empty disease reference
				$errors_list[]=_("ERROR ImportDiseases: A disease with an empty reference was found!");
				$Error=TRUE;
			}
			if ($Error==FALSE && isset($diseases[$disease['reference']])) {
				// Reference already used
				$errors_list[]=sprintf(_("ERROR ImportDiseases: A disease with the reference %s was already defined!"),$disease['reference']);
				$Error=TRUE;
			}

			if ($Error==FALSE) {
				// Check periods
				$nb_weekly=0;
				$nb_monthly=0;
				$nb_none=0;
				foreach ($disease['values'] as $key=>$values) {
					if ($values['period']=="Weekly")  $nb_weekly++;
					if ($values['period']=="Monthly") $nb_monthly++;
					if ($values['period']=="None")    $nb_none++;
				}
				$disease['nb_weekly']  = $nb_weekly;
				$disease['nb_monthly'] = $nb_monthly;
				$disease['nb_none']    = $nb_none;
				// Check if it's an alert
				if ($disease['reference']==$config["alert_reference"]) {
					// Alert
					if ($nb_weekly>0 || $nb_monthly>0 || $nb_none==0) {
						// Bad values
						$errors_list[]=_("ERROR ImportDiseases: An alert definition must contain at last one value with None as period (and no Weekly or Monthly period)");
						$Error=TRUE;
					}
				} else {
					// Normal disease
					if (($nb_weekly==0 && $nb_monthly==0) || $nb_none>0) {
						// Bad values
						$errors_list[]=sprintf(_("ERROR ImportDiseases: The disease %s definition must contain at last one value with Weekly or Monthly as period (and no None period)"),$disease['reference']);
						$Error=TRUE;	
					}
				}
			}

			// Get constraints
			if ($Error==FALSE)
			{
				$disease['constraints']=array();
				$constraint_nodes=xml_find_sub_childs_by_tag($disease_nodes[$i], "constraints", "constraint");
				$unicityConstraints = array() ;

				for ($j=0; $j<count($constraint_nodes); $j++) {
					$constraint = array();
					$constraint['referencevalue_from']=xml_get_child_value($constraint_nodes[$j],"referencevalue_from");
					$constraint['operator']=xml_get_child_value($constraint_nodes[$j],"operator");
					$constraint['referencevalue_to']=xml_get_child_value($constraint_nodes[$j],"referencevalue_to");
					$constraint['period']=xml_get_child_value($constraint_nodes[$j],"period");

					//Check period
					if ($constraint['period']===FALSE || trim($constraint['period'])==""){
						$errors_list[]=sprintf(_("ERROR ImportDiseases: A Period must be defined in the constraint for disease %s "), $disease['reference']);
						$Error=TRUE;
					}

					//Check operator
					if ($constraint['operator']===FALSE || trim($constraint['operator'])==""){
						$errors_list[]=sprintf(_("ERROR ImportDiseases: An operator must be defined in the constraint for disease %s "), $disease['reference']);
						$Error=TRUE;
					}

					//Check if reference_from is not empty and correspond to an existing one
					if ($constraint['referencevalue_from']===FALSE || trim($constraint['referencevalue_from'])==""){
						$errors_list[]=sprintf(_("ERROR ImportDiseases: A reference value From must be defined in the constraint for disease %s "), $disease['reference']);
						$Error=TRUE;
					}
					else
					{
						$refValue = $constraint['referencevalue_from'] ;
						if (! array_key_exists($refValue.'-'.$constraint['period'],$disease['values']))
						{
							$errors_list[]=sprintf(_("ERROR ImportDiseases: The reference value From %s is unknown for the constraint for disease %s "), $refValue, $disease['reference']);
							$Error=TRUE;
						}
					}

					if ($constraint['referencevalue_to']===FALSE || trim($constraint['referencevalue_to'])==""){
						$errors_list[]=sprintf(_("ERROR ImportDiseases: A reference value To must be defined in the constraint for disease %s "), $disease['reference']);
						$Error=TRUE;
					}
					else
					{
						$refValue = $constraint['referencevalue_to'] ;
						if (! array_key_exists($refValue.'-'.$constraint['period'],$disease['values']))
						{
							$errors_list[]=sprintf(_("ERROR ImportDiseases: The reference value To %s is unknown for the constraint for disease %s "), $refValue, $disease['reference']);
							$Error=TRUE;
						}
					}

					if ($Error===FALSE)	{

						$unicity = $constraint['referencevalue_from'].'-'.$constraint['operator'].'-'.$constraint['referencevalue_to'].'-'.$constraint['period'];

						if (array_search($unicity,$unicityConstraints ) === FALSE){
							$unicityConstraints[]=$unicity;
							$disease['constraints'][] = $constraint ;
						}
						else{
							$Error = TRUE ;
							$errors_list[]=sprintf(_("ERROR ImportDiseases: A constraint for disease %s already exists "), $disease['reference']);
						}
					}

				}
			}

			// Adding disease if no error found
			if ($Error==FALSE) {
				$diseases[$disease['reference']]=$disease;
			}
		}

		// ==================================
		// Step #2 - Checking keyword unicity
		// ==================================
		
		// Checking if we had errors
		if (count($errors_list)==0) {
			$AllDiseasesKeywords=array();
			foreach ($diseases as $reference=>$disease) {
				// Check disease keyword
				for($i=0; $i<count($disease['keywords']); $i++) {
					if (array_search($disease['keywords'][$i], $AllDiseasesKeywords)===FALSE) {
						// Adding keyword
						$AllDiseasesKeywords[]=$disease['keywords'][$i];
					} else {
						// Error, already defined
						$errors_list[]=sprintf(_("ERROR ImportDiseases: The disease keyword %s for disease %s is already defined!"),$disease['keywords'][$i],$disease['reference']);
						$Error=TRUE;	
					}
				}
				// Check values keywords
				$KeywordsWeekly=array();
				$KeywordsMonthly=array();
				$KeywordsNone=array();
				foreach ($disease['values'] as $key=>$value) {
					if ($value['period']=='Weekly') {
						for($i=0; $i<count($value['keywords']); $i++) {
							if (array_search($value['keywords'][$i], $KeywordsWeekly)===FALSE) {
								// Adding keyword
								$KeywordsWeekly[]=$value['keywords'][$i];
							} else {
								// Error, already defined
								$errors_list[]=sprintf(_("ERROR ImportDiseases: The weekly value keyword %s for disease %s is already defined!"),$value['keywords'][$i],$disease['reference']);
								$Error=TRUE;	
							}
						}
					}
					if ($value['period']=='Monthly') {
						for($i=0; $i<count($value['keywords']); $i++) {
							if (array_search($value['keywords'][$i], $KeywordsMonthly)===FALSE) {
								// Adding keyword
								$KeywordsMonthly[]=$value['keywords'][$i];
							} else {
								// Error, already defined
								$errors_list[]=sprintf(_("ERROR ImportDiseases: The monthly value keyword %s for disease %s is already defined!"),$value['keywords'][$i],$disease['reference']);
								$Error=TRUE;	
							}
						}
					}
					if ($value['period']=='None') {
						for($i=0; $i<count($value['keywords']); $i++) {
							if (array_search($value['keywords'][$i], $KeywordsNone)===FALSE) {
								// Adding keyword
								$KeywordsNone[]=$value['keywords'][$i];
							} else {
								// Error, already defined
								$errors_list[]=sprintf(_("ERROR ImportDiseases: The alert value keyword %s is already defined!"),$value['keywords'][$i]);
								$Error=TRUE;	
							}
						}
					}
				}
			}
		}

		return($errors_list);
	}

    /**
     * Import diseases configuration
     *
     * @param $bdd
     * @param $diseases
     */
    function ImportDiseases($bdd, $diseases)
    {
        foreach ($diseases as $reference => $disease) {
            // Add disease
            $Values = array(
                "disease" => "'" . $bdd->escape($disease['reference']) . "'",
                "name" => "'" . $bdd->escape($disease['name']) . "'",
                "keywords" => "'" . $bdd->escape(implode(",", $disease['keywords'])) . "'",
            );
            db_Insert($bdd, __FUNCTION__, __LINE__, __FILE__, "ses_diseases", $Values,false);

            // Insert values
            $Position_Weekly = 1;
            $Position_Monthly = 1;
            $Position_Alert = 1;
            foreach ($disease['values'] as $key => $value) {
                // Get the correct position
                if ($value['period'] == 'Weekly') {
                    $Position = $Position_Weekly;
                    $Position_Weekly++;
                }
                if ($value['period'] == 'Monthly') {
                    $Position = $Position_Monthly;
                    $Position_Monthly++;
                }
                if ($value['period'] == 'None') {
                    $Position = $Position_Alert;
                    $Position_Alert++;
                }
                // Add the value definition
                $Values = array(
                    "disease" => "'" . $bdd->escape($disease['reference']) . "'",
                    "value" => "'" . $bdd->escape($value['reference']) . "'",
                    "period" => "'" . $bdd->escape($value['period']) . "'",
                    "position" => $Position,
                    "datatype" => "'" . $bdd->escape($value['type']) . "'",
                    "mandatory" => $value['mandatory'],
                    "keywords" => "'" . $bdd->escape(implode(",", $value['keywords'])) . "'",
                );
                db_Insert($bdd, __FUNCTION__, __LINE__, __FILE__, "ses_disease_values", $Values,false);
            }

            // Insert constraints
            foreach ($disease['constraints'] as $key => $value) {
                // Add the value definition
                $Values = array(
                    "disease" => "'" . $bdd->escape($disease['reference']) . "'",
                    "period" => "'" . $bdd->escape($value['period']) . "'",
                    "value_from" => "'" . $bdd->escape($value['referencevalue_from']) . "'",
                    "operator" => "'" . $bdd->escape($value['operator']) . "'",
                    "value_to" => "'" . $bdd->escape($value['referencevalue_to']) . "'"
                );

                db_Insert($bdd, __FUNCTION__, __LINE__, __FILE__, "ses_disease_constraints", $Values,false);
            }
        }
    }
?>