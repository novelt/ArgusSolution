-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------



-- Dumping structure for procedure avadar_dev.usp_sesdashboard_Sites_getAlertPreferredGateway
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Sites_getAlertPreferredGateway`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Sites_getAlertPreferredGateway`(
	IN `siteId` INT
)
BEGIN

DECLARE alertPreferredGateway varchar(255);
DECLARE parentSiteId int;

SET @@SESSION.max_sp_recursion_depth=100;

SELECT s.alertPreferredGateway, srs.FK_ParentId INTO alertPreferredGateway, parentSiteId
FROM sesdashboard_sites s
INNER JOIN sesdashboard_sites_relationship srs ON s.id = srs.FK_SiteId
WHERE (srs.FK_DimDateToId IS NULL OR srs.FK_DimDateToId > (SELECT id FROM sesdashboard_indicatordimdate WHERE fullDate = CURDATE()))
AND s.id = siteId;

if(alertPreferredGateway is null and parentSiteId is not null) then
	call usp_sesdashboard_Sites_getAlertPreferredGateway(parentSiteId);
else
	-- just in case when later we want to retrieve the result into a variable
	set @alertPreferredGateway = alertPreferredGateway;
	
	select alertPreferredGateway;
end if;

END//
DELIMITER ;


