-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	This stored procedure returns all the children sites of the given site(s) Id, in the temp table tmp_children_sites
	Changes:
		- 2017-06-08 - MT-oSOM4uGg-cases-dashboard-include-cases-which-site-is-disabled: added new parameter includeAllSites. If TRUE, source from sesdashboard_sites. Else, source from uvw_sesdashboard_sites.
        - 2017-12-06: WHO-Delete_Sites_timely : Join the sesdashboard_sites_relationship table And add DimDate parameters
		- 2018-03-26 - MT-nHfk0HiK-handle-historical-geography-in-indicators : Added new parameter dimDateTypeCode to handle historical geography
		- 2018-03-28 - MT-nHfk0HiK-handle-historical-geography-in-indicators: Replaced some "truncate" with "delete", it caused a transaction commit
		- 2018-04-19 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf : Fixed the stored procedure to correctly handle historical geography
		- 2018-04-25 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf : Created new column 'name' in the temp table tmp_children_sites
*/

-- Dumping structure for procedure avadar_20170322_v6.1.1.usp_sesdashboard_Sites_buildChildrenHierarchy
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Sites_buildChildrenHierarchy`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Sites_buildChildrenHierarchy`(
	IN `siteIds` TEXT,
	IN `sorted` INT,
	IN `includeAllSites` INT,
	IN `includeInputSiteIds` INT,
	IN `maxSitesRecursiveLevel` INT,
	IN `dimDateFromId` INT,
	IN `dimDateToId` INT,
	IN `dimDateTypeCode` TEXT
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
	
	call usp_sesdashboard_log(@ERROR, 'usp_sesdashboard_Sites_buildChildrenHierarchy', CONCAT_WS('', 'Error the retrieval of children sites. Error message: [',message_text,'], SQL state code: [',sqlstate_code,'], MySQL errno: [',mysql_errno,'].'));
	
	RESIGNAL;
END;

CREATE TEMPORARY TABLE IF NOT EXISTS tmp_children_sites (
	siteId int(11) not null,
	siteLevel int(11) not null,
	isLeaf int(11) not null,
	isDeleted tinyint(1) null,
	sortOrder int(11) not null,
	name varchar(255) null,
	path varchar(2000) null
);

delete from tmp_children_sites; -- do not use truncate, it will commit the transaction

SET @@SESSION.max_sp_recursion_depth=100;
SET @siteSortOrder = 0;
SET @maxSitesRecursiveLevel = maxSitesRecursiveLevel;
SET @sitesRecursiveLevel = 0;

if(@debugMode is not null) then
		call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_buildChildrenHierarchy', CONCAT_WS('', 'Received following parameters: siteIds:[',siteIds,'], sorted:[',sorted,'], includeAllSites:[',includeAllSites,'], includeInputSiteIds:[',includeInputSiteIds,'], maxSitesRecursiveLevel:[',maxSitesRecursiveLevel,'], dimDateFromId:[',dimDateFromId,'], dimDateToId:[',dimDateToId,']'));
	end if;

IF(sorted = 0) THEN
	if(@debugMode is not null) then
		call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_buildChildrenHierarchy', CONCAT_WS('', 'Starting to get children sites by calling usp_sesdashboard_Sites_loadChildrenHierarchy'));
	end if;

	call usp_sesdashboard_Sites_loadChildrenHierarchy(siteIds, includeAllSites, includeInputSiteIds, 0, dimDateFromId, dimDateToId, dimDateTypeCode);
ELSE
	if(@debugMode is not null) then
		call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_buildChildrenHierarchy', CONCAT_WS('', 'Starting to get children sites by calling usp_sesdashboard_Sites_loadSortedChildrenHierarchy'));
	end if;

	-- it is not possible to create a cursor on a "array" of siteIds received in parameter.
	-- the only way to do this is to fill a table with the siteIds, and loop on it.
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_children_sites_recursiveLevel (
		siteId int(11) not null,
		name VARCHAR(255),
		isDeleted tinyint(1) null,
		recursiveLevel int(11) not null
	) ENGINE=MEMORY;
	
	delete from tmp_children_sites_recursiveLevel; -- do not use truncate, it will commit the transaction
	
	call usp_sesdashboard_Sites_loadSortedChildrenHierarchy(siteIds, includeAllSites, null, includeInputSiteIds, 0, dimDateFromId, dimDateToId, dimDateTypeCode);
END IF;

END//
DELIMITER ;
