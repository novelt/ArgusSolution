-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.24 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             9.5.0.5273
-- --------------------------------------------------------

/*
	This function centralizes the logic used to generate the SQL condition when writting a SQL request to get active sites.

	Changes done:
		- 2018-04-16 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf: Created the function.
		- 2018-04-19 - MT-b5GEd6NL-indicators-parent-sites-having-no-active-children-must-be-considered-as-leaf: Removed the parameter includeAllSites
*/

-- Dumping structure for function avadar.ufn_sesdashboard_Sites_getActiveSitesSQLConditions
DROP FUNCTION IF EXISTS `ufn_sesdashboard_Sites_getActiveSitesSQLConditions`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `ufn_sesdashboard_Sites_getActiveSitesSQLConditions`(
	`dimDateTypeCode` TEXT,
	`dimDateFromId` INT,
	`dimDateToId` INT,
	`tableAlias` TEXT
) RETURNS longtext CHARSET utf8
READS SQL DATA
DETERMINISTIC
BEGIN

/* The final result that will be returned by the function */
DECLARE conditions TEXT;

DECLARE firstPeriodDimDateId INT;

/* Their values depend on the parameter dimDateTypeCode */
DECLARE dimDateFromIdColumnName TEXT;
DECLARE dimDateToIdColumnName TEXT;

if(dimDateTypeCode = 'Weekly' or dimDateTypeCode = 'WeeklyEpidemiologic') then
	SET dimDateFromIdColumnName = 'FK_WeekDimDateFromId';
	SET dimDateToIdColumnName = 'FK_WeekDimDateToId';
elseif(dimDateTypeCode = 'Monthly') then
	SET dimDateFromIdColumnName = 'FK_MonthDimDateFromId';
	SET dimDateToIdColumnName = 'FK_MonthDimDateToId';
else
	SET dimDateFromIdColumnName = 'FK_DimDateFromId';
	SET dimDateToIdColumnName = 'FK_DimDateToId';
end if;

if (dimDateToId is null) then
	if (dimDateFromId is null) then
		-- Get Latest hierarchy, by selecting the first day of the period (weekly, monthly or daily)
		if(dimDateTypeCode = 'Weekly') then
			SET firstPeriodDimDateId = (SELECT min(d2.id) FROM sesdashboard_indicatordimdate d1 inner join sesdashboard_indicatordimdate d2 on d1.weekOfYear = d2.weekOfYear and d1.calendarYear = d2.calendarYear WHERE d1.fullDate = CURDATE());
		elseif(dimDateTypeCode = 'WeeklyEpidemiologic') then
			SET firstPeriodDimDateId = (SELECT min(d2.id) FROM sesdashboard_indicatordimdate d1 inner join sesdashboard_indicatordimdate d2 on d1.epiWeekOfYear = d2.epiWeekOfYear and d1.epiYear = d2.epiYear WHERE d1.fullDate = CURDATE());
		elseif(dimDateTypeCode = 'Monthly') then
			SET firstPeriodDimDateId = (SELECT min(d2.id) FROM sesdashboard_indicatordimdate d1 inner join sesdashboard_indicatordimdate d2 on d1.monthOfYear = d2.monthOfYear and d1.calendarYear = d2.calendarYear WHERE d1.fullDate = CURDATE());
		else
			SET firstPeriodDimDateId = (SELECT id FROM sesdashboard_indicatordimdate WHERE fullDate = CURDATE());
		end if;

		SET conditions = CONCAT('(',tableAlias,'.',dimDateToIdColumnName,' IS NULL OR ',tableAlias,'.',dimDateToIdColumnName,' >= ', firstPeriodDimDateId,' )');
	 else
		SET conditions = CONCAT('(',tableAlias,'.',dimDateToIdColumnName,' IS NULL OR ',tableAlias,'.',dimDateToIdColumnName,' >= ', dimDateFromId,' )');
	end if;

else
	if (dimDateFromId is null) then
		SET conditions = CONCAT('',tableAlias,'.',dimDateFromIdColumnName,' <= ', dimDateToId,' ');
	else
		SET conditions = CONCAT('(',tableAlias,'.',dimDateToIdColumnName,' IS NULL OR ',tableAlias,'.',dimDateToIdColumnName,' >= ', dimDateFromId,') AND ',tableAlias,'.',dimDateFromIdColumnName,' <= ', dimDateToId,' ');
	end if;
end if;

return conditions;

END//
DELIMITER ;
