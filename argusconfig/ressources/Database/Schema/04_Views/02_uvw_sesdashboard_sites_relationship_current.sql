-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	View uvw_sesdashboard_sites_relationship_current.
	Returns the content of the table sesdashboard_sites_relationship, by showing if the sites are currently active in the periods Daily, Weekly, WeeklyEpidemiologic and Monthly.
	
	Modifications done:
		- 2018-03-29 - MT-nHfk0HiK-handle-historical-geography-in-indicators: Created the view.
*/


-- Dumping structure for view avadar_test.uvw_sesdashboard_sites_relationship_current
DROP VIEW IF EXISTS `uvw_sesdashboard_sites_relationship_current`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `uvw_sesdashboard_sites_relationship_current` AS
select
	srs.*,
	CASE WHEN (srs.FK_DimDateToId IS NULL OR srs.FK_DimDateToId >= (SELECT id FROM sesdashboard_indicatordimdate WHERE fullDate = CURDATE())) THEN 1 ELSE 0 END as isActiveDaily,
	CASE WHEN (srs.FK_WeekDimDateToId IS NULL OR srs.FK_WeekDimDateToId >= (SELECT min(d2.id) FROM sesdashboard_indicatordimdate d1 inner join sesdashboard_indicatordimdate d2 on d1.weekOfYear = d2.weekOfYear and d1.calendarYear = d2.calendarYear WHERE d1.fullDate = CURDATE())) THEN 1 ELSE 0 END as isActiveWeekly,
	CASE WHEN (srs.FK_WeekDimDateToId IS NULL OR srs.FK_WeekDimDateToId >= (SELECT min(d2.id) FROM sesdashboard_indicatordimdate d1 inner join sesdashboard_indicatordimdate d2 on d1.epiWeekOfYear = d2.epiWeekOfYear and d1.epiYear = d2.epiYear WHERE d1.fullDate = CURDATE())) THEN 1 ELSE 0 END as isActiveWeeklyEpidemiologic,
	CASE WHEN (srs.FK_MonthDimDateToId IS NULL OR srs.FK_MonthDimDateToId >= (SELECT min(d2.id) FROM sesdashboard_indicatordimdate d1 inner join sesdashboard_indicatordimdate d2 on d1.monthOfYear = d2.monthOfYear and d1.calendarYear = d2.calendarYear WHERE d1.fullDate = CURDATE())) THEN 1 ELSE 0 END as isActiveMonthly
from sesdashboard_sites_relationship srs
inner join uvw_sesdashboard_sites_relationship_last_changes lc on srs.FK_SiteId = lc.FK_SiteId and srs.FK_DimDateFromId = lc.FK_DimDateFromId;


