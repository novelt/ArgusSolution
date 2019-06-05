-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	Procedure usp_sesdashboard_Sites_getLocale
	Returns the site's locale

	Modifications done:
		- 2017-12-06: WHO-Delete_Sites_timely : Join the sesdashboard_sites_relationship table

*/

-- Dumping structure for procedure avadar_dev_6.0.3.usp_sesdashboard_Sites_getLocale
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Sites_getLocale`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Sites_getLocale`(
	IN `siteId` INT

)
BEGIN

DECLARE locale varchar(100);
DECLARE parentSiteId int;

SET @@SESSION.max_sp_recursion_depth=100;

SELECT s.locale, srs.FK_ParentId INTO locale, parentSiteId
FROM sesdashboard_sites s
INNER JOIN sesdashboard_sites_relationship srs ON s.id = srs.FK_SiteId
WHERE (srs.FK_DimDateToId IS NULL OR srs.FK_DimDateToId > (SELECT id FROM sesdashboard_indicatordimdate WHERE fullDate = CURDATE()))
AND s.id = siteId;

if(locale is null and parentSiteId is not null) then
	call usp_sesdashboard_Sites_getLocale(parentSiteId);
else
	-- just in case when later we want to retrieve the result into a variable
	set @siteLocale = locale;
	
	select locale;
end if;

END//
DELIMITER ;


