-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	Stored procedure usp_sesdashboard_Sites_loadSortedChildrenHierarchy

	This stored procedure stores in the temp table tmp_children_sites the received site ids, and their children, recursively.
	The children are sorted following this way:
		Site level 0
			Site level 1
				Site level 2
					Site level 3
					Site level 3
				Site level 2
					Site level 3
					Site level 3
					Site level 3
						Site level 4
				Site level 2
			Site level 1
				Site level 2
				...
				
	Changes:
		- 2017-06-08 - MT-oSOM4uGg-cases-dashboard-include-cases-which-site-is-disabled: added new parameter includeAllSites. If TRUE, source from sesdashboard_sites. Else, source from uvw_sesdashboard_sites.
		- 2017-07-06 - MT-sBlvSV9f-dashboard-indicators-excel-export-error-404-afro-data: increaed the limit of group_concat_max_len from 16384 to 4194304. The limit of 16384 is already too low.
		- 2018-03-26 - MT-nHfk0HiK-handle-historical-geography-in-indicators : Added new parameter dimDateTypeCode. The sites hierarchy will be loaded in function of this new parameter, to handle historical geography.
		- 2018-03-27 - MT-nHfk0HiK-handle-historical-geography-in-indicators : Removed the variable sites_source, no more needed
		- 2018-03-28 - MT-nHfk0HiK-handle-historical-geography-in-indicators : Re-handle the parameter includeAllSites to include deleted sites
		- 2018-04-19 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf : Fixed the stored procedure to correctly handle historical geography
		- 2018-04-25 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf : Store the site name in the temp table tmp_children_sites, and fix about the isLeaf column
		- 2018-08-08 - Develop: increased the value of group_concat_max_len to the highest possible value in 32bits.
*/

-- Dumping structure for procedure avadar_20170322_v6.1.1.usp_sesdashboard_Sites_loadSortedChildrenHierarchy
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Sites_loadSortedChildrenHierarchy`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Sites_loadSortedChildrenHierarchy`(
	IN `siteIds` TEXT,
	IN `includeAllSites` INT,
	IN `parentSitePath` TEXT,
	IN `includeInputSiteIds` INT,
	IN `currentSitesRecursiveLevel` INT,
	IN `dimDateFromId` INT,
	IN `dimDateToId` INT,
	IN `dimDateTypeCode` TEXT
)
BEGIN

DECLARE done INT DEFAULT FALSE; -- used for the cursor
DECLARE currentSiteId INT(11);
DECLARE currentSiteName varchar(255);
DECLARE currentSiteIsDeleted tinyint(1);
DECLARE currentSitePath varchar(2000);
DECLARE firstPeriodDimDateId INT;

/* variables for error handling */
DECLARE sqlstate_code char(5);
DECLARE message_text text;
DECLARE mysql_errno int;
DECLARE errorMsgText TEXT;

/* Their values depend on the parameter dimDateTypeCode */
DECLARE dimDateFromIdColumnName TEXT;
DECLARE dimDateToIdColumnName TEXT;

/* Where Clause for request */
DECLARE activeSiteSQLConditions TEXT;
DECLARE siteIsDeletedSQL_clause TEXT;
DECLARE activeChildrenSiteSQLConditions TEXT;
DECLARE where_clause TEXT;
DECLARE where_and TEXT; -- will contain 'WHERE' or 'AND'

/* Cursor  : USE the WHERE CLAUSE dynamic here */
DECLARE cursor_siteIds CURSOR FOR
	select srl.siteId, srl.name, isDeleted
	from tmp_children_sites_recursiveLevel srl	
	where srl.recursiveLevel = currentSitesRecursiveLevel;

/* handler for the cursor */
DECLARE CONTINUE HANDLER FOR NOT FOUND
BEGIN
	set done=1;
	if(@debugMode is not null) then
		call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'In the loop for the siteIds [',siteIds,']. Cursor handler not found. Set done = 1.'));
	end if;
END;

/* handler for the error handling*/
DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
	GET DIAGNOSTICS CONDITION 1
	sqlstate_code = RETURNED_SQLSTATE, mysql_errno = MYSQL_ERRNO, message_text = MESSAGE_TEXT;
	
	call usp_sesdashboard_log(@ERROR, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'Error during the retrieval of children sites. Error message: [',message_text,'], SQL state code: [',sqlstate_code,'], MySQL errno: [',mysql_errno,'].'));
	
	RESIGNAL;
END;

if(@debugMode is not null) then
	call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'Received following parameters: siteIds:[',siteIds,'], includeAllSites:[',includeAllSites,'], parentSitePath:[',parentSitePath,'], includeInputSiteIds:[',includeInputSiteIds,'], currentSitesRecursiveLevel:[',currentSitesRecursiveLevel,'], dimDateFromId:[',dimDateFromId,'], dimDateToId:[',dimDateToId,']'));
end if;

SET currentSitesRecursiveLevel = currentSitesRecursiveLevel + 1;
SET group_concat_max_len = 4294967295; -- needed for the GROUP_CONCAT function

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

/* ---- Fill the table tmp_children_sites_recursiveLevel ---- */
if(siteIds is null) then
	-- in this case, retrieve the root sites
	SET @selectRootSitesRequest = CONCAT_WS('', 'SET @parentSiteIds = (select GROUP_CONCAT(distinct srs.FK_SiteId) from sesdashboard_sites_relationship srs ',where_clause, where_and, 'srs.FK_ParentId is null);');
		
	if(@debugMode is not null) then
        call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'The given siteId parameter is null. Retrieving the root sites to start the recursive loop, by running the following query: [',@selectRootSitesRequest,']'));
    end if;
	
	PREPARE stmt_selectRootSites FROM @selectRootSitesRequest;
	EXECUTE stmt_selectRootSites;
	DEALLOCATE PREPARE stmt_selectRootSites;
	
	SET siteIds = @parentSiteIds;	
end if;

if (includeInputSiteIds = 1) then
    -- insert the sites in tmp_children_sites_recursiveLevel
    -- we will loop on it. It only permits to lop on the received siteIds parameters.
    SET @insertchildrenSitesRequest = CONCAT_WS('', '
        insert into tmp_children_sites_recursiveLevel(siteId, recursiveLevel, name, isDeleted)
        select srs.FK_SiteId, ',currentSitesRecursiveLevel,', srs.name, ',siteIsDeletedSQL_clause,' as isDeleted
        from sesdashboard_sites_relationship srs '
        ,where_clause, where_and, 'srs.FK_SiteId in (',siteIds,')'
    );

    if(@debugMode is not null) then
        call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'Running the following query to insert the received site ids in tmp_children_sites_recursiveLevel: [',@insertchildrenSitesRequest,']'));
    end if;

    PREPARE stmt_fill_tmp_children_sites_recursiveLevel FROM @insertchildrenSitesRequest;
    EXECUTE stmt_fill_tmp_children_sites_recursiveLevel;
    DEALLOCATE PREPARE stmt_fill_tmp_children_sites_recursiveLevel;
end if;
/* ---------------------------------------------------------- */

if(@debugMode is not null) then
	call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'Start of the loop for the siteIds [',siteIds,'].'));
end if;

-- retrieve the join conditions on childs. It will be used in the cursor below.
SET activeChildrenSiteSQLConditions = (select CONCAT(' AND ', ufn_sesdashboard_Sites_getActiveSitesSQLConditions(dimDateTypeCode, dimDateFromId, dimDateToId, 'child')));

OPEN cursor_siteIds;
	read_loop: LOOP
		-- for each siteId received in parameter
		FETCH cursor_siteIds INTO currentSiteId, currentSiteName, currentSiteIsDeleted;
		if done = 1 then leave read_loop; end if;
		
		SET @siteSortOrder = @siteSortOrder + 1;
		SET currentSitePath = (select CONCAT_WS('', parentSitePath, '|', currentSiteName));		
	
		if(@debugMode is not null) then
			call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'In the loop for the siteIds [',siteIds,']. Starting to process the currentSiteId:[',currentSiteId,']. CurrentSiteName:[',currentSiteName,'], currentSitesRecursiveLevel:[',currentSitesRecursiveLevel,'], currentSitePath:[',currentSitePath,'], siteSortOrder:[',@siteSortOrder,'], maxSitesRecursiveLevel:[',@maxSitesRecursiveLevel,'], cursor done:[',done,']'));
		end if;
				
		-- insert the sites in the temp table
		SET @insertchildrenSitesRequest = CONCAT_WS('', '
			insert into tmp_children_sites(siteId, siteLevel, isLeaf, isDeleted, sortOrder, path, name)
			select distinct srs.FK_SiteId, srs.`level`, CASE WHEN child.id IS NULL THEN 1 ELSE 0 END as isLeaf, ',currentSiteIsDeleted,', @siteSortOrder, \'',REPLACE(currentSitePath, '''', ''''''),'\', srs.name
			from sesdashboard_sites_relationship srs
			left join sesdashboard_sites_relationship child on child.FK_ParentId = srs.FK_SiteId ',activeChildrenSiteSQLConditions,'
			',where_clause, where_and, 'srs.FK_SiteId = ',currentSiteId,';'
		);
		
		if(@debugMode is not null) then
			call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'Running the following query to insert the site [',currentSiteId,'] in the table tmp_children_sites: [',@insertchildrenSitesRequest,']'));
		end if;

		PREPARE stmt_insertchildrenSites FROM @insertchildrenSitesRequest;
		EXECUTE stmt_insertchildrenSites;
		DEALLOCATE PREPARE stmt_insertchildrenSites;
		
		if(@maxSitesRecursiveLevel IS NULL OR @maxSitesRecursiveLevel >= currentSitesRecursiveLevel) THEN
			SET @retrieveChildrenSitesRequest = CONCAT_WS('', 'SET @childrenSiteIds = (select GROUP_CONCAT(distinct srs.FK_SiteId)
			                                                                    FROM sesdashboard_sites_relationship srs ' ,
			                                                                    where_clause, where_and, 'srs.FK_ParentId = ',currentSiteId,');');
			
			PREPARE stmt_retrieveChildrenSitesRequest FROM @retrieveChildrenSitesRequest;
			EXECUTE stmt_retrieveChildrenSitesRequest;
			DEALLOCATE PREPARE stmt_retrieveChildrenSitesRequest;
						
			if(@childrenSiteIds is not null) then
				if(@debugMode is not null) then
					call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'The current site id [',currentSiteId,'] has children sites:[',@childrenSiteIds,']. Calling usp_sesdashboard_Sites_loadSortedChildrenHierarchy(',@childrenSiteIds,',',currentSitePath,',',currentSitesRecursiveLevel,').'));
				end if;
			
				-- and give their ids to this same stored procedure, to add them and their parents, etc...
				call usp_sesdashboard_Sites_loadSortedChildrenHierarchy(@childrenSiteIds, includeAllSites, currentSitePath, 1, currentSitesRecursiveLevel, dimDateFromId, dimDateToId, dimDateTypeCode);
			else
				if(@debugMode is not null) then
					call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'The current site id [',currentSiteId,'] has no children sites'));
				end if;
			end if;
		END IF;
		
		if(@debugMode is not null) then
			call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'In the loop for the siteIds [',siteIds,']. Finished to process the currentSiteId:[',currentSiteId,']. currentSiteName:[',currentSiteName,'], currentSitesRecursiveLevel:[',currentSitesRecursiveLevel,'], currentSitePath:[',currentSitePath,'], siteSortOrder:[',@siteSortOrder,'], maxSitesRecursiveLevel:[',@maxSitesRecursiveLevel,'], cursor done:[',done,']'));
		end if;
		
		if(@debugMode is not null) then
			call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'delete from tmp_children_sites_recursiveLevel where siteId = ',currentSiteId,';'));
		end if;
		
		delete from tmp_children_sites_recursiveLevel where siteId = currentSiteId;
	END LOOP;
CLOSE cursor_siteIds;

if(@debugMode is not null) then
	call usp_sesdashboard_log(@DEBUG, 'usp_sesdashboard_Sites_loadSortedChildrenHierarchy', CONCAT_WS('', 'End of the loop for the siteIds [',siteIds,'].'));
end if;

END//
DELIMITER ;
