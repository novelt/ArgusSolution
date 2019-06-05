-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*
	Changes done:
		- 2018-03-27 - MT-nHfk0HiK-handle-historical-geography-in-indicators : Removed some weird duplicated code.
	
*/


-- Dumping structure for procedure avadar_test.usp_sesdashboard_indicatorDimDates_getFirstDays
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorDimDates_getFirstDays`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_indicatorDimDates_getFirstDays`(
	IN `dimDateTypeCode` TEXT,
	IN `dateFrom` TEXT,
	IN `dateTo` TEXT
)
    COMMENT 'Calculates the first day weekOfYear, epiWeekOfYear or monthOfYear in function of the given date range. The result is stored in the temp table tmp_dimDates'
sp:BEGIN

DECLARE errorMsgText TEXT;

DECLARE datesKey TEXT;
DECLARE dimDateColumn TEXT;

if(@debugMode is not null) then
	call usp_sesdashboard_log(@DEBUG, 'usp_indicatorDimDates_getFirstDays', CONCAT_WS('', 'Received parameter: dimDateTypeCode: [',dimDateTypeCode,'], dateFrom:[',dateFrom,'],  dateTo:[',dateTo,']'));
end if;

/* Create the temp table tmp_indicatorData into which the indicator calculators will store the calculated data */
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_dimDates (
	dimDateId int(11) not null,
	`key` varchar(70),	
	dimDateColumn varchar(50) not null,
	dateFrom varchar(10),
	dateTo varchar(10),
	INDEX `PRIMARY_KEY` (`dimDateId`),
	INDEX `IDX_key` (`key`)
) ENGINE=MEMORY;

if(dimDateTypeCode = 'WeeklyEpidemiologic') then
	SET dimDateColumn = 'epiWeekOfYear';
else 
	SET errorMsgText = CONCAT_WS('', 'The dimDateTypeCode [',dimDateTypeCode,'] is not handled to calculate the first days from [',dateFrom,'] to [',dateTo,']');
	SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = errorMsgText;
end if;

-- if the result has already been calculated --> leave now, the result is already present in the temp table
set datesKey = CONCAT_WS('', dimDateTypeCode, dateFrom, dateTo);

if(@debugMode is not null) then
	call usp_sesdashboard_log(@DEBUG, 'usp_indicatorDimDates_getFirstDays', CONCAT_WS('', 'Calculated key: [',datesKey,'].'));
end if;

if exists(select * from tmp_dimDates d where d.`key`=datesKey) then
	if(@debugMode is not null) then
		call usp_sesdashboard_log(@DEBUG, 'usp_indicatorDimDates_getFirstDays', CONCAT_WS('', 'Nothing to calculate, leave.'));
	end if;
	LEAVE sp;
end if;

SET @select = CONCAT('select min(d2.id) as id, \'',datesKey,'\' as `key`, \'',dimDateColumn,'\' as dimDateColumn');
SET @from = CONCAT('
	from sesdashboard_indicatordimdate d1
	inner join sesdashboard_indicatordimdate d2 on d1.epiYear = d2.epiYear and d1.',dimDateColumn,' = d2.',dimDateColumn);

SET @andWhere = ' where ';

if(dateFrom is not null) then
	SET @select = CONCAT(@select, ', \'',dateFrom,'\' as dateFrom');
	SET @from = CONCAT(@from, @andWhere, 'd1.fullDate >= \'', dateFrom,'\'');
	SET @andWhere = ' and ';
else
	SET @select = CONCAT(@select, ', null as dateFrom');
end if;

if(dateTo is not null) then
	SET @select = CONCAT(@select, ', \'',dateTo,'\' as dateTo');
	SET @from = CONCAT(@from, @andWhere, 'd1.fullDate <= \'', dateTo, '\'');
	SET @andWhere = ' and ';
else
	SET @select = CONCAT(@select, ', null as dateTo');
end if;

SET @from = CONCAT(@from, ' group by d2.epiYear, d2.', dimDateColumn);
SET @from = CONCAT(@from, ' order by d2.id');

SET @request = CONCAT('insert into tmp_dimDates ', @select, @from);

if(@debugMode is not null) then
	call usp_sesdashboard_log(@DEBUG, 'usp_indicatorDimDates_getFirstDays', CONCAT_WS('', 'Running the following command: [',@request,'].'));
end if;

PREPARE stmt_getFirstDays FROM @request;
EXECUTE stmt_getFirstDays;
DEALLOCATE PREPARE stmt_getFirstDays;

if(@infoMode is not null) then
	call usp_sesdashboard_log(@DEBUG, 'usp_indicatorDimDates_getFirstDays', CONCAT_WS('', 'IndicatorDimDates calculated for the range [',dateFrom,'-',dateTo,', dimDateTypeCode]: [',(select GROUP_CONCAT(dimDateId) from tmp_dimDates d where d.`key` = datesKey),']'));
end if;

END//
DELIMITER ;
