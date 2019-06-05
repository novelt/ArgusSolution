<?php

	// ---------------------------------------------
	// Gestion des années / semaines épidémiologique
	// ---------------------------------------------
	// La première semaine de l'année est celle ayant au moins 4 jours dans la nouvelle annnée
	// ---------------------------------------------

	// Get Timestamp for first day of the week, depending variable "epi_first_day"
	// epi_first_day = 1 => Monday
	// epi_first_day = 1 => Saturday
	// epi_first_day = 7 => Sunday
	function Epi2Timestamp ($Year, $Week)
	{
		global $config;
		$ts = null ;

		// Case when Epi first Day is Monday (ISO 8601)
		if (intval($config['epi_first_day']) == 1){
			$date = new DateTime();
			$date->setISODate($Year, $Week, 1);
			$ts = mktime(0,0,0,date("m",$date->getTimestamp()), date("d",$date->getTimestamp()),date("Y",$date->getTimestamp()));
		}
		else
		{
			$firstDayOfWeekOne = GetTimeStampForFirstDayOfWeek(intval($config['epi_first_day']), $Year);

			//Now we get the first day of Week number '$Week'
			$ts = strtotime('+'.(($Week-1) *7).' days',$firstDayOfWeekOne);

			return $ts ;
		}

		return $ts ;

		/*
				// Case when Epi first Day is Sunday
				elseif (intval($config['epi_first_day']) == 7) {
					// We need to find first Saturday of January
					$saturdayOfFirstWeek = GetEndDayOfFirstWeek('Saturday', $Year);

					// We get the Sunday , first day of Week 1
					$firstDayOfWeekOne = strtotime('Last Sunday',$saturdayOfFirstWeek);

					//Now we get the first day of Week number '$Week'
					$ts = strtotime('+'.(($Week-1) *7).' days',$firstDayOfWeekOne);
				}
				//Case When Epi first Day is Saturday
				elseif (intval($config['epi_first_day']) == 6) {
					// We need to find first Friday of January
					$fridayOfFirstWeek = GetEndDayOfFirstWeek('Friday', $Year);

					// We get the Saturday , first day of Week 1
					$firstDayOfWeekOne = strtotime('Last Saturday',$fridayOfFirstWeek);

					//Now we get the first day of Week number '$Week'
					$ts = strtotime('+'.(($Week-1) *7).' days',$firstDayOfWeekOne);
				}*/
	}

	// Get epidemiology year and week from unix timestamp
	function Timestamp2Epi ($ts) {
		global $config;
		$result = array() ;

		// Case when Epi first Day is Monday (ISO 8601)
		if (intval($config['epi_first_day']) == 1) {
			$result['Year'] = intval(date("o",$ts)) ;
			$result['Week'] = intval(date("W",$ts));
		}
		else
		{
			$firstDayNameOfWeek = GetDayOfWeek(intval($config['epi_first_day']));
			$lastDayNameOfWeek = GetDayOfWeek(intval($config['epi_first_day']-1));

			// Get Name of Day of week from Timestamp
			$dayName = date('l',$ts);
			$firstDayOfWeek = $ts ;
			if ($dayName != $firstDayNameOfWeek){
				$firstDayOfWeek = strtotime('Last '.$firstDayNameOfWeek, $firstDayOfWeek);
			}

			$yearFirstDayOfWeek = intval(date("Y",$firstDayOfWeek)) ;
			$yearLastDayOfWeek = intval(date("Y",strtotime('Next '.$lastDayNameOfWeek,$firstDayOfWeek)));

			// We have got the Year of the first day of Week
			$result['Year'] = intval(date("Y",$firstDayOfWeek));

			//If years are different, we need to test the last day of week to know if this week in in next year or not
			if ($yearFirstDayOfWeek < $yearLastDayOfWeek)
			{
				$lastDayOfWeek = strtotime('Next '.$lastDayNameOfWeek,$firstDayOfWeek);
				$dayNumber = date('d', $lastDayOfWeek);

				if ($dayNumber >= 4)
					$result['Year'] ++ ;

			}

			$firstDayOfWeekOne = GetTimeStampForFirstDayOfWeek(intval($config['epi_first_day']), $result['Year']);

			// We do the difference between sunday of week one and our sunday
			$diffDays = abs($firstDayOfWeek - $firstDayOfWeekOne)/60/60/24 ;

			// We get the week number
			$weekNumber = round($diffDays /7) +1 ;

			$result['Week'] = $weekNumber ;

		}

		/*
		// Case when Epi first Day is Sunday
		elseif (intval($config['epi_first_day']) == 7) {

			// Get First Day of week from Timestamp
			// We get the Sunday , first day of Week
			$dayName = date('l',$ts);
			$firstDayOfWeek = $ts ;
			if ($dayName != 'Sunday'){
				$firstDayOfWeek = strtotime('Last Sunday',$firstDayOfWeek);
			}

			// We have got the Year of the first day of Week
			$result['Year'] = intval(date("Y",$firstDayOfWeek));

			// We need to find first Saturday of January
			$saturdayOfFirstWeek = GetLastDayOfFirstWeek('Saturday', $result['Year']);

			// We get the Sunday , first day of Week 1
			$firstDayOfWeekOne = strtotime('Last Sunday',$saturdayOfFirstWeek);

			// We do the difference between sunday of week one and our sunday
			$diffDays = abs($firstDayOfWeek - $firstDayOfWeekOne)/60/60/24 ;

			// We get the week number
			$weekNumber = round($diffDays /7) +1 ;

			$result['Week'] = $weekNumber ;
		}
		//Case When Epi first Day is Saturday
		elseif (intval($config['epi_first_day']) == 6) {

			// Get First Day of week from Timestamp
			// We get the Saturday , first day of Week
			$dayName = date('l',$ts);
			$firstDayOfWeek = $ts ;
			if ($dayName != 'Saturday'){
				$firstDayOfWeek = strtotime('Last Saturday',$firstDayOfWeek);
			}

			// We have got the Year of the first day of Week
			$result['Year'] = intval(date("Y",$firstDayOfWeek));

			// We need to find first Friday of January
			$fridayOfFirstWeek = GetLastDayOfFirstWeek('Friday', $result['Year']);

			// We get the Saturday , first day of Week 1
			$firstDayOfWeekOne = strtotime('Last Saturday',$fridayOfFirstWeek);

			// We do the difference between Saturday of week one and our Saturday
			$diffDays = abs($firstDayOfWeek - $firstDayOfWeekOne)/60/60/24 ;

			// We get the week number
			$weekNumber = round($diffDays /7) +1 ;

			$result['Week'] = $weekNumber ;

		}*/

		return($result);
	}

	/*
	 * Get Time stamp for first day of week regarding the configuration of first day
	 *
	 */
	function GetTimeStampForFirstDayOfWeek($dayNumber, $year)
	{
		$firstDayOfWeek = GetDayOfWeek($dayNumber);
		$lastDayOfWeek = GetDayOfWeek($dayNumber -1);

		// We need to find last day of first week
		$lastDayOfFirstWeek = GetLastDayOfFirstWeek($lastDayOfWeek, $year);

		// We get the first day of Week 1
		$firstDayOfWeekOne = strtotime('Last '.$firstDayOfWeek, $lastDayOfFirstWeek);

		return $firstDayOfWeekOne;
	}


	/*
	 * Get the last day of first week if this end day is at least number 4 of month
	 */
	function GetLastDayOfFirstWeek($day, $year){
		$firstDayOfFirstWeek = strtotime ('First '.$day, mktime(0,0,0,01,0,$year)) ;
		// Day number of this day
		$dayNumber = date('d', $firstDayOfFirstWeek);

		// We need that this day Number must be >= 4
		// If not we take the same day of next week
		if ($dayNumber < 4){
			$firstDayOfFirstWeek = strtotime('Next '.$day, $firstDayOfFirstWeek);
		}

		return $firstDayOfFirstWeek;
	}

	/* Return name of Week day
	 *	1 : Monday
	 *	2 : Tuesday
	 *	3 : Wednesday
	 *	4 : Thursday
	 *	5 : Friday
	 *	6 : Saturday
	 *	7 : Sunday
	 */
	function GetDayOfWeek($dayNumber)
	{
		/*  Julian Day
         * 0 : Monday
         * 6 : Sunday
         */
		return jddayofweek($dayNumber-1, 1);
	}

?>