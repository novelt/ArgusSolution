-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------

/*
	Changes done:
		- 2018-03-28 - MT-nHfk0HiK-handle-historical-geography-in-indicators: Replaced a "truncate" with "delete", it caused a transaction commit
*/

-- Dumping structure for procedure avadar_20170322_v6.1.1.usp_sesdashboard_Logs_buildLevels
DROP PROCEDURE IF EXISTS `usp_sesdashboard_Logs_buildLevels`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_Logs_buildLevels`(
	IN `logLevelCode` TEXT,
	IN `sessionId` INT

)
BEGIN

CREATE TEMPORARY TABLE IF NOT EXISTS tmp_logLevels (
	id int(11) not null,
	code text not null
);

delete from tmp_logLevels; -- do not use truncate, it will commit the transaction

insert into tmp_logLevels
select
	ll.id, ll.code
from sesdashboard_loglevel ll
where ll.id >= (select id from sesdashboard_loglevel where code like logLevelCode)
order by ll.id;

/* variables used for the logging during the whole calculation */
SET @logLevelId = (select id from sesdashboard_loglevel where code like logLevelCode);

SET @DEBUG = (select id from sesdashboard_loglevel where code like 'DEBUG');
SET @INFO = (select id from sesdashboard_loglevel where code like 'INFO');
SET @ERROR = (select id from sesdashboard_loglevel where code like 'ERROR');

SET @debugMode = (select id from tmp_logLevels where code like 'DEBUG');
SET @infoMode = (select id from tmp_logLevels where code like 'INFO');
set @sessionId = sessionId;

END//
DELIMITER ;


