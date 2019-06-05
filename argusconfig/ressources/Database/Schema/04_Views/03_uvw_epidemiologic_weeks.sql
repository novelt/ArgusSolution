-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*	
	Changes done:
	 - 2017-29-12: expose the epiYear instead of the calendar year. Bugfix related to the calculation of the week 52 of 2017.
*/

-- Dumping structure for view avadar_test.uvw_epidemiologic_weeks
DROP VIEW IF EXISTS `uvw_epidemiologic_weeks`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `uvw_epidemiologic_weeks`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` VIEW `uvw_epidemiologic_weeks` AS
	SELECT min(id) as date_id, min(fullDate) as firstdayOfWeek, epiYear as calendarYear, epiWeekOfYear
	FROM sesdashboard_indicatordimdate
	group by epiYear, epiWeekOfYear
