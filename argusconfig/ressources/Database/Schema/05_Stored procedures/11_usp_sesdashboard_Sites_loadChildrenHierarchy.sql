-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	Stored procedure usp_sesdashboard_Sites_loadChildrenHierarchy.

	This stored procedure stores in a temp table the received site ids, and their children, recursively.
	Changes:
		- 2017-03-23 nf: added sortOrder
		- 2017-06-08 - MT-oSOM4uGg-cases-dashboard-include-cases-which-site-is-disabled: added new parameter includeAllSites. If TRUE, source from sesdashboard_sites. Else, source from uvw_sesdashboard_sites.
		- 2017-07-06 - MT-sBlvSV9f-dashboard-indicators-excel-export-error-404-afro-data: increaed the limit of group_concat_max_len from 16384 to 4194304. The limit of 16384 is already too low.
        - 2017-12-06: WHO-Delete_Sites_timely : Join the sesdashboard_sites_relationship table And add DimDate parameters
		- 2018-03-26 - MT-nHfk0HiK-handle-historical-geography-in-indicators : Added new parameter dimDateTypeCode. The sites hierarchy will be loaded in function of this new parameter, to handle historical geography.
		- 2018-03-28 - MT-nHfk0HiK-handle-historical-geography-in-indicators : Re-handle the parameter includeAllSites to include deleted sites
		- 2018-04-19 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf : Fixed the stored procedure to correctly handle historical geography
		- 2018-04-25 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf : Fix about the isLeaf column
		- 2018-08-08 - Develop: increased the value of group_concat_max_len to the highest possible value in 32bits.
*/

-- Dumping structure for procedure avadar_20170322_v6.1.1.usp_sesdashboard_Sites_loadChildrenHierarchy
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Sites_loadChildrenHierarchy`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Sites_loadChildrenHierarchy`(
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

DECLARE retrieveChildrenSites INT DEFAULT TRUE;
DECLARE firstPeriodDimDateId INT;

/* Where Clause for request */
DECLARE activeSiteSQLConditions TEXT;
DECLARE activeChildrenSiteSQLConditions TEXT;
DECLARE siteIsDeletedSQL_clause TEXT;
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

	call usp_sesdashboard_log(@ERROR, 'usp_sesdashboard_Sites_loadChildrenHierarchy', CONCAT_WS('', 'Error during the retrieval of children sites. Error message: [',message_text,'], SQL state code: [',sqlstate_code,'], MySQL errno: [',mysql_errno,'].'));

	RESIGNAL;
END;

if(@debugMode is not null) then
	call usp_sesdashboard_log(@INFO, 'usp_sesdashboard_Sites_loadChildrenHierarchy', CONCAT_WS('', 'Received following parameters: siteIds:[',siteIds,'], includeAllSites:[',includeAllSites,'], includeInputSiteIds:[',includeInputSiteIds,'], currentSitesRecursiveLevel:[',currentSitesRecursiveLevel,'], dimDateFromId:[',dimDateFromId,'], dimDateToId:[',dimDateToId,']'));
end if;

SET activeSiteSQLConditions = (select ufn_sesdashboard_Sites_getActiveSitesSQLConditions(dimDateTypeCode, dimDateFromId, dimDateToId, 'srs'));
SET where_and = ' WHERE ';

if(includeAllSites = 1) then
	SET siteIsDeletedSQL_clause = CONCAT('CASE WHEN ',activeSiteSQLConditions,' THEN 0 ELSE 1 END');
	SET where_clause = NULL;
else
	SET siteIsDeletedSQL_clause = 0;
	SET where_clause = CONCAT(where_and, activeSiteSQLConditions);
	SET where_and = ' AND ';
end if;
	
if(siteIds is null) then
	-- in this case, retrieve the root sites
	SET @selectRootSitesRequest = CONCAT_WS('', 'SET @parentSiteIds = (select GROUP_CONCAT(distinct srs.FK_SiteId) from sesdashboard_sites_relationship srs ',where_clause, where_and, 'srs.FK_ParentId is null);');
		
	if(@debugMode is not null) then
        call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadChildrenHierarchy', CONCAT_WS('', 'The given siteId parameter is null. Retrieving the root sites to start the recursive loop, by running the following query: [',@selectRootSitesRequest,']'));
    end if;
	
	PREPARE stmt_selectRootSites FROM @selectRootSitesRequest;
	EXECUTE stmt_selectRootSites;
	DEALLOCATE PREPARE stmt_selectRootSites;
	
	SET siteIds = @parentSiteIds;	
end if;

if (includeInputSiteIds = 1) then
	-- retrieve the join conditions on childs.
	SET activeChildrenSiteSQLConditions = (select CONCAT(' AND ', ufn_sesdashboard_Sites_getActiveSitesSQLConditions(dimDateTypeCode, dimDateFromId, dimDateToId, 'child')));

    -- insert the sites in the temp table
    SET @insertchildrenSitesRequest = CONCAT_WS('', '
        insert into tmp_children_sites(siteId, siteLevel, isLeaf, isDeleted, sortOrder, name)
        select distinct srs.FK_SiteId, srs.`level`, CASE WHEN child.id IS NULL THEN 1 ELSE 0 END as isLeaf, ',siteIsDeletedSQL_clause,' as isDeleted, ',@siteSortOrder,', srs.name
        from sesdashboard_sites_relationship srs
        left join sesdashboard_sites_relationship child on child.FK_ParentId = srs.FK_SiteId',activeChildrenSiteSQLConditions ,'
        ',where_clause, where_and, 'srs.FK_SiteId in (',siteIds,')'
    );
	
	if(@debugMode is not null) then
		call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadChildrenHierarchy', CONCAT_WS('', 'Running the following query to insert the received site ids in tmp_children_sites: [',@insertchildrenSitesRequest,']'));
	end if;

    PREPARE stmt_insertchildrenSites FROM @insertchildrenSitesRequest;
    EXECUTE stmt_insertchildrenSites;
    DEALLOCATE PREPARE stmt_insertchildrenSites;
end if;

SET currentSitesRecursiveLevel = currentSitesRecursiveLevel + 1;

if(retrieveChildrenSites = TRUE AND (@maxSitesRecursiveLevel IS NULL OR @maxSitesRecursiveLevel >= currentSitesRecursiveLevel)) THEN	
	-- retrieve their children
	SET group_concat_max_len = 4294967295;
	SET @retrieveChildrenSitesRequest = CONCAT_WS('', 'SET @childrenSiteIds = (select GROUP_CONCAT(distinct srs.FK_SiteId)
	                                                                    FROM sesdashboard_sites_relationship srs ' ,
	                                                                    where_clause, where_and, 'srs.FK_ParentId in (',siteIds,'))');

	SET @siteSortOrder = @siteSortOrder + 1;

	PREPARE stmt_retrieveChildrenSites FROM @retrieveChildrenSitesRequest;
	EXECUTE stmt_retrieveChildrenSites;
	DEALLOCATE PREPARE stmt_retrieveChildrenSites;

	if(@childrenSiteIds is not null) then
		-- and give their ids to this same stored procedure, to add them and their parents, etc...
		call usp_sesdashboard_Sites_loadChildrenHierarchy(@childrenSiteIds, includeAllSites, 1, currentSitesRecursiveLevel, dimDateFromId, dimDateToId, dimDateTypeCode);
	end if;
END IF;

END//
DELIMITER ;