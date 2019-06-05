-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	This stored procedure returns all the parents sites of the given site(s) Id, in the temp table tmp_parents_sites.
	Changes:
		- 2018-03-27 - MT-nHfk0HiK-handle-historical-geography-in-indicators: Created the stored proc by duplicating usp_sesdashboard_Sites_getChildrenSites
*/

-- Dumping structure for procedure avadar_20170322_v6.1.1.usp_sesdashboard_Sites_getParentsSites
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Sites_getParentsSites`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Sites_getParentsSites`(
	IN `siteIds` TEXT,
	IN `includeAllSites` INT,
	IN `includeInputSiteIds` INT,
	IN `maxSitesRecursiveLevel` INT,
	IN `dimDateFromId` INT,
	IN `dimDateToId` INT,
	IN `dimDateTypeCode` TEXT,
	IN `sessionId` TEXT,
	IN `logLevelCode` TEXT
)
BEGIN

/* variables for error handling */
DECLARE sqlstate_code char(5);
DECLARE message_text text;
DECLARE mysql_errno int;
DECLARE errorMsgText TEXT;

/* handler for the error handling*/
DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
	GET DIAGNOSTICS CONDITION 1
	sqlstate_code = RETURNED_SQLSTATE, mysql_errno = MYSQL_ERRNO, message_text = MESSAGE_TEXT;
	
	call usp_sesdashboard_log(@ERROR, 'usp_sesdashboard_Sites_getParentsSites', CONCAT_WS('', 'Error during the retrieval of parents sites. Error message: [',message_text,'], SQL state code: [',sqlstate_code,'], MySQL errno: [',mysql_errno,'].'));
END;

/* Used for the logging */
CALL usp_sesdashboard_Logs_buildLevels(logLevelCode, sessionId);

if(@debugMode is not null) then
	call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_getParentsSites', CONCAT_WS('', 'Retrieve parents sites with the following parameters: sessionId:[',sessionId,'], logLevelCode[',logLevelCode,'], siteIds:[',siteIds,'], includeAllSites:[',includeAllSites,'], includeInputSiteIds:[',includeInputSiteIds,'], maxSitesRecursiveLevel:[',maxSitesRecursiveLevel,'], dimDateFromId:[',dimDateFromId,'], dimDateToId:[',dimDateToId,']'));
end if;

call usp_sesdashboard_Sites_buildParentsHierarchy(siteIds, includeAllSites, includeInputSiteIds,  maxSitesRecursiveLevel, dimDateFromId, dimDateToId, dimDateTypeCode);

END//
DELIMITER ;