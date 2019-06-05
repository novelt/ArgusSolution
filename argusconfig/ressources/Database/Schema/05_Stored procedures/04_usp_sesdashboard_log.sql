-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5151
-- --------------------------------------------------------



-- Dumping structure for procedure avadar_20170322_v6.1.1.usp_sesdashboard_log
DROP PROCEDURE IF EXISTS `usp_sesdashboard_log`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_log`(
	IN `logLevelId` INT,
	IN `source` TEXT,
	IN `message` TEXT

)
BEGIN

insert into sesdashboard_log(creationDate, source, logLevelId, calculationSessionId, `message`)
values (NOW(), source, logLevelId, @sessionId, `message`);

END//
DELIMITER ;


