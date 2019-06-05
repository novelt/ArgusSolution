-- --------------------------------------------------------
-- Hôte :                        127.0.0.1
-- Version du serveur:           5.6.24 - MySQL Community Server (GPL)
-- SE du serveur:                Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Export de la structure de la table gateway_test. ses_gateway_queue
CREATE TABLE IF NOT EXISTS `ses_gateway_queue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `phoneNumber` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `message` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `gatewayId` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pending` bit(1) DEFAULT NULL,
  `sent` datetime DEFAULT NULL,
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `updateDate` datetime DEFAULT NULL,
  `failure` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- Les données exportées n'étaient pas sélectionnées.
-- Export de la structure de la table gateway_test. ses_incoming_sms
CREATE TABLE IF NOT EXISTS `ses_incoming_sms` (
  `phoneNumber` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `message` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `gatewayId` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ses_gateway_devices` (
  `gatewayId` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `operator` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `manufacturer` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `model` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdk` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `versionName` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `battery` int(11) DEFAULT NULL,
  `power` int(11) DEFAULT NULL,
  `updateDate` datetime NOT NULL,
  `pollInterval` int(11) DEFAULT NULL,
  PRIMARY KEY (`gatewayId`),
  KEY `gatewayId` (`gatewayId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Dumping structure for view gateway_test.uvw_sms_reception_check
DROP VIEW IF EXISTS `uvw_sms_reception_check`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` VIEW `uvw_sms_reception_check` AS
select
	queue.id,
	queue.gatewayId as 'Send from',
	queue.phoneNumber as 'Send to',
	queue.message,
	queue.pending,
	queue.sent as 'Sent date',
	queue.creationDate as 'Send creation date',
	queue.updateDate as 'Send update date',
	queue.failure,
	case when received.message is null then 0 else 1 end as received,
	received.phoneNumber as 'Received from',
	received.creationDate as 'Reception date',
	TIMESTAMPDIFF(SECOND, queue.sent, received.creationDate) as 'Reception delay'
from ses_gateway_queue queue
left join ses_incoming_sms received on queue.message = received.message;

-- Les données exportées n'étaient pas sélectionnées.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
