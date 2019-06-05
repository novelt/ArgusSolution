-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*
	Stored procedure usp_sesdashboard_IndicatorScopes_buildAndLoadDependencies.
	
	This recurssive stored procedure load all the impacted scopeIds to recalculate.
	
	Changes:
		- 2017-07-06 - MT-sBlvSV9f-dashboard-indicators-excel-export-error-404-afro-data: increaed the limit of group_concat_max_len from 16384 to 4194304. The limit of 16384 is already too low.	
		- 2018-03-26 - MT-nHfk0HiK-handle-historical-geography-in-indicators: Renamed usp_sesdashboard_Sites_loadHierarchy into usp_sesdashboard_Sites_loadParentsHierarchy, and usp_sesdashboard_Sites_buildHierarchy into usp_sesdashboard_Sites_buildParentsHierarchy
		- 2018-03-27 - MT-nHfk0HiK-handle-historical-geography-in-indicators: Rewritten the stored proc by duplicating usp_sesdashboard_Sites_loadChildrenHierarchy. Added some logging.
		- 2018-03-28 - MT-nHfk0HiK-handle-historical-geography-in-indicators : Re-handle the parameter includeAllSites to include deleted sites
		- 2018-04-19 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf : Fixed the stored procedure to correctly handle historical geography-in-indicators
		- 2018-04-25 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf : Store the site name in the temp table tmp_children_sites, and fix about the isLeaf column
		- 2018-08-08 - Develop: increased the value of group_concat_max_len to the highest possible value in 32bits.
*/

-- Dumping structure for procedure avadar_test.usp_sesdashboard_Sites_loadParentsHierarchy
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Sites_loadParentsHierarchy`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Sites_loadParentsHierarchy`(
	IN `siteIds` TEXT,
	IN `includeAllSites` INT,
	IN `includeInputSiteIds` INT,
	IN `currentSitesRecursiveLevel` INT,
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

DECLARE retrieveParentsSites INT DEFAULT TRUE;
DECLARE firstPeriodDimDateId INT;

/* Where Clause for request */
DECLARE activeParentsSiteSQLConditions TEXT;
DECLARE siteIsDeletedSQL_clause TEXT;
DECLARE activeChildrenSiteSQLConditions TEXT;
DECLARE where_clause TEXT;
DECLARE where_and TEXT; -- will contain 'WHERE' or 'AND'

/* Their values depend on the parameter dimDateTypeCode */
DECLARE dimDateFromIdColumnName TEXT;
DECLARE dimDateToIdColumnName TEXT;

/* handler for the error handling*/
DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
	GET DIAGNOSTICS CONDITION 1
	sqlstate_code = RETURNED_SQLSTATE, mysql_errno = MYSQL_ERRNO, message_text = MESSAGE_TEXT;

	call usp_sesdashboard_log(@ERROR, 'usp_sesdashboard_Sites_loadParentsHierarchy', CONCAT_WS('', 'Error during the retrieval of parents sites. Error message: [',message_text,'], SQL state code: [',sqlstate_code,'], MySQL errno: [',mysql_errno,'].'));

	RESIGNAL;
END;

if(@debugMode is not null) then
	call usp_sesdashboard_log(@INFO, 'usp_sesdashboard_Sites_loadParentsHierarchy', CONCAT_WS('', 'Received following parameters: siteIds:[',siteIds,'], includeAllSites:[',includeAllSites,'], includeInputSiteIds:[',includeInputSiteIds,'], currentSitesRecursiveLevel:[',currentSitesRecursiveLevel,'], dimDateFromId:[',dimDateFromId,'], dimDateToId:[',dimDateToId,']'));
end if;

SET activeParentsSiteSQLConditions = (select ufn_sesdashboard_Sites_getActiveSitesSQLConditions(dimDateTypeCode, dimDateFromId, dimDateToId, 'srs'));
SET where_and = ' WHERE ';

if(includeAllSites = 1) then
	SET siteIsDeletedSQL_clause = CONCAT('CASE WHEN ',activeParentsSiteSQLConditions,' THEN 0 ELSE 1 END');
	SET where_clause = NULL;
else	
	SET siteIsDeletedSQL_clause = 0;
	SET where_clause = CONCAT(where_and, activeParentsSiteSQLConditions);
	SET where_and = ' AND ';
end if;

if(siteIds is null) then
	-- in this case, simple just return every sites. And the job is done.
	SET @selectEverySitesRequest = CONCAT_WS('', 'SET @siteIds = (select GROUP_CONCAT(srs.FK_SiteId) from sesdashboard_sites_relationship srs ',where_clause,');');

	if(@debugMode is not null) then
		call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadParentsHierarchy', CONCAT_WS('', 'Running the following query to insert ALL site ids in tmp_parents_sites: [',@selectEverySitesRequest,']'));
	end if;
	
	PREPARE stmt_selectEverySites FROM @selectEverySitesRequest;
	EXECUTE stmt_selectEverySites;
	DEALLOCATE PREPARE stmt_selectEverySites;

	SET retrieveParentsSites = FALSE;
else
	SET @siteIds = siteIds;
end if;

if (includeInputSiteIds = 1) then
	-- retrieve the join conditions on childs.
	SET activeChildrenSiteSQLConditions = (select CONCAT(' AND ', ufn_sesdashboard_Sites_getActiveSitesSQLConditions(dimDateTypeCode, dimDateFromId, dimDateToId, 'child')));
	
    -- insert the sites in the temp table
    SET @insertparentsSitesRequest = CONCAT_WS('', '
        insert into tmp_parents_sites(siteId, FK_ParentId, siteLevel, isLeaf, isDeleted, sortOrder, name)
        select distinct srs.FK_SiteId, srs.FK_ParentId, srs.`level`, CASE WHEN child.id IS NULL THEN 1 ELSE 0 END as isLeaf, ', siteIsDeletedSQL_clause ,' as isDeleted, ',@siteSortOrder,', srs.name
        from sesdashboard_sites_relationship srs
        left join sesdashboard_sites_relationship child on child.FK_ParentId = srs.FK_SiteId  ',activeChildrenSiteSQLConditions,'
		',where_clause, where_and, 'srs.FK_SiteId in (',@siteIds,')'
    );
	
	if(@debugMode is not null) then
		call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadParentsHierarchy', CONCAT_WS('', 'Running the following query to insert the received site ids in tmp_parents_sites: [',@insertparentsSitesRequest,']'));
	end if;

    PREPARE stmt_insertparentsSites FROM @insertparentsSitesRequest;
    EXECUTE stmt_insertparentsSites;
    DEALLOCATE PREPARE stmt_insertparentsSites;
end if;

SET currentSitesRecursiveLevel = currentSitesRecursiveLevel + 1;

if(retrieveParentsSites = TRUE AND (@maxSitesRecursiveLevel IS NULL OR @maxSitesRecursiveLevel >= currentSitesRecursiveLevel)) THEN
	-- retrieve their parents
	SET group_concat_max_len = 4294967295;
	SET @retrieveParentsSitesRequest = CONCAT_WS('', 'SET @parentsSiteIds = (select GROUP_CONCAT(distinct srs.FK_ParentId)
	                                                                    FROM sesdashboard_sites_relationship srs ' ,
	                                                                    where_clause, where_and, 'srs.FK_SiteId in (',@siteIds,'))');
																		
	SET @siteSortOrder = @siteSortOrder + 1;
	
	if(@debugMode is not null) then
		call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadParentsHierarchy', CONCAT_WS('', 'Running the following query to retrieve the parent site ids: [',@retrieveParentsSitesRequest,']'));
	end if;

	PREPARE stmt_retrieveParentsSites FROM @retrieveParentsSitesRequest;
	EXECUTE stmt_retrieveParentsSites;
	DEALLOCATE PREPARE stmt_retrieveParentsSites;

	if(@parentsSiteIds is not null) then
		-- and give their ids to this same stored procedure, to add them and their parents, etc...
		call usp_sesdashboard_Sites_loadParentsHierarchy(@parentsSiteIds, includeAllSites, 1, currentSitesRecursiveLevel, dimDateFromId, dimDateToId, dimDateTypeCode);
	end if;
END IF;

END//
DELIMITER ;
