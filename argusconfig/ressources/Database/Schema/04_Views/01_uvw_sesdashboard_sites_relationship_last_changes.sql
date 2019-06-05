-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	View uvw_sesdashboard_sites_relationship_last_changes.	
	Returns the last changes done onby reading the table sesdashboard_sites_relationship. This view has been created for the view uvw_sesdashboard_sites_relationship_current, as it is not possible to have sub-selects in views.
	
	Modifications done:
		- 2018-03-29 - MT-nHfk0HiK-handle-historical-geography-in-indicators: Created the view.
*/


-- Dumping structure for view avadar_test.uvw_sesdashboard_sites_relationship_last_changes
DROP VIEW IF EXISTS `uvw_sesdashboard_sites_relationship_last_changes`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `uvw_sesdashboard_sites_relationship_last_changes` AS
-- Get the last changes done on sites, by getting the highest FK_DimDateFromId
select srs.FK_SiteId, max(srs.FK_DimDateFromId) as FK_DimDateFromId from sesdashboard_sites_relationship srs
group by srs.FK_SiteId;