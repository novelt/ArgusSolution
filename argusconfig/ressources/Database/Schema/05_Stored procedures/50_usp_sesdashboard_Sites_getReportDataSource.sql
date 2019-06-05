-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	Procedure usp_sesdashboard_Sites_getReportDataSource
	Returns the site's inherited report data source.
	
	Modifications done:
		- 2018-07-02: MT-srVEpBUO-implement-csv-upload-backend: created the stored procedure

*/

-- Dumping structure for procedure usp_sesdashboard_Sites_getReportDataSource
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Sites_getReportDataSource`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Sites_getReportDataSource`(
	IN `siteId` INT


)
BEGIN

DECLARE reportDataSourceId varchar(100);
DECLARE parentSiteId int;

SET @@SESSION.max_sp_recursion_depth=100;

SELECT s.reportDataSourceId, srs.FK_ParentId INTO reportDataSourceId, parentSiteId
FROM sesdashboard_sites s
INNER JOIN sesdashboard_sites_relationship srs ON s.id = srs.FK_SiteId
WHERE (srs.FK_DimDateToId IS NULL OR srs.FK_DimDateToId > (SELECT id FROM sesdashboard_indicatordimdate WHERE fullDate = CURDATE()))
AND s.id = siteId;

if(reportDataSourceId is null and parentSiteId is not null) then
	call usp_sesdashboard_Sites_getReportDataSource(parentSiteId);
else
	-- just in case when later we want to retrieve the result into a variable
	set @siteReportDataSourceId = reportDataSourceId;
	
	select reportDataSourceId;
end if;

END//
DELIMITER ;


