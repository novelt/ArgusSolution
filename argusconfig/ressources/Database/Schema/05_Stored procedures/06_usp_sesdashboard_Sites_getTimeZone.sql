-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	Procedure usp_sesdashboard_Sites_getTimeZone
	Returns the site's timezone

	Modifications done:
		- 2017-12-06: WHO-Delete_Sites_timely : Join the sesdashboard_sites_relationship table

*/

-- Dumping structure for procedure avadar_dev.usp_sesdashboard_Sites_getTimeZone
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Sites_getTimeZone`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Sites_getTimeZone`(
	IN `siteId` INT


)
BEGIN

DECLARE timezone varchar(100);
DECLARE parentSiteId int;

SET @@SESSION.max_sp_recursion_depth=100;

SELECT s.timezone, srs.FK_ParentId INTO timezone, parentSiteId
FROM sesdashboard_sites s
INNER JOIN sesdashboard_sites_relationship srs ON s.id = srs.FK_SiteId
WHERE (srs.FK_DimDateToId IS NULL OR srs.FK_DimDateToId > (SELECT id FROM sesdashboard_indicatordimdate WHERE fullDate = CURDATE()))
AND s.id = siteId;

if(timezone is null and parentSiteId is not null) then
	call usp_sesdashboard_Sites_getTimeZone(parentSiteId);
else
	-- just in case when later we want to retrieve the result into a variable
	set @siteTimezone = timezone;
	
	select timezone;
end if;

END//
DELIMITER ;


