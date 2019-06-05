-- MySQL dump 10.13  Distrib 5.6.24, for Win32 (x86)
--
-- Host: localhost    Database: avadar
-- ------------------------------------------------------
-- Server version	5.6.24

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `contact_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `active` bit(1) NOT NULL,
  `emailAddress` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `otherPhoneNumber` varchar(255) DEFAULT NULL,
  `phoneNumber` varchar(255) DEFAULT NULL,
  `imei` varchar(255) DEFAULT NULL,
  `imei2` varchar(255) DEFAULT NULL,
  `alertPreferredGateway` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`contact_id`),
  UNIQUE KEY `contact_id` (`contact_id`),
  UNIQUE KEY `phoneNumber` (`phoneNumber`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `frontline_group`
--

DROP TABLE IF EXISTS `frontline_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frontline_group` (
  `path` varchar(255) NOT NULL,
  `parentPath` varchar(255) NOT NULL DEFAULT '',
  `ses_name` varchar(100) NOT NULL DEFAULT '',
  `ses_reminderWeekly` int(11) NOT NULL DEFAULT '0',
  `ses_reminderMonthly` int(11) NOT NULL DEFAULT '0',
  `ses_alert_preferred_gateway` varchar(255) DEFAULT NULL,
  `cascading_alert` BIT(1) DEFAULT NULL,
  PRIMARY KEY (`path`),
  UNIQUE KEY `path` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupmembership`
--

DROP TABLE IF EXISTS `groupmembership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupmembership` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `contact_contact_id` bigint(20) NOT NULL,
  `group_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `contact_contact_id` (`contact_contact_id`,`group_path`),
  KEY `FKBA9C3F955A18C292` (`contact_contact_id`),
  KEY `FKBA9C3F95DAF23DFD` (`group_path`),
  CONSTRAINT `FKBA9C3F955A18C292` FOREIGN KEY (`contact_contact_id`) REFERENCES `contact` (`contact_id`),
  CONSTRAINT `FKBA9C3F95DAF23DFD` FOREIGN KEY (`group_path`) REFERENCES `frontline_group` (`path`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `refresh_tokens`
--

DROP TABLE IF EXISTS `refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refresh_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refresh_token` varchar(128) NOT NULL,
  `username` varchar(255) NOT NULL,
  `valid` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9BACE7E1C74F2195` (`refresh_token`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_contact_information`
--

DROP TABLE IF EXISTS `ses_contact_information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_contact_information` (
  `contact_phoneNumber` varchar(255) NOT NULL,
  `android_version` varchar(255) NOT NULL DEFAULT '0.0',
  PRIMARY KEY (`contact_phoneNumber`),
  KEY `contact_phoneNumber` (`contact_phoneNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_data`
--

DROP TABLE IF EXISTS `ses_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  `reception` datetime NOT NULL,
  `exported` datetime DEFAULT NULL,
  `contactName` varchar(255) NOT NULL,
  `contactPhoneNumber` varchar(255) NOT NULL,
  `disease` varchar(100) NOT NULL,
  `period` varchar(45) NOT NULL,
  `periodStart` datetime NOT NULL,
  `reportId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Index_SiteEmetteur` (`path`),
  KEY `Index_DateReception` (`reception`),
  KEY `Index_DateTraitement` (`exported`),
  KEY `Index_Maladie` (`disease`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_datavalues`
--

DROP TABLE IF EXISTS `ses_datavalues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_datavalues` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `FK_dataId` bigint(20) unsigned NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `exported` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ses_datavalues_ses_data` (`FK_dataId`),
  CONSTRAINT `FK_ses_datavalues_ses_data` FOREIGN KEY (`FK_dataId`) REFERENCES `ses_data` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_disease_constraints`
--

DROP TABLE IF EXISTS `ses_disease_constraints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_disease_constraints` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `disease` varchar(100) NOT NULL,
  `period` varchar(45) NOT NULL,
  `value_from` varchar(100) NOT NULL,
  `operator` varchar(100) NOT NULL,
  `value_to` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `_disease_constraints_diseases` (`disease`),
  CONSTRAINT `_disease_constraints_diseases` FOREIGN KEY (`disease`) REFERENCES `ses_diseases` (`disease`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_disease_values`
--

DROP TABLE IF EXISTS `ses_disease_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_disease_values` (
  `disease` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  `period` varchar(45) NOT NULL,
  `position` int(11) NOT NULL,
  `datatype` varchar(45) NOT NULL,
  `mandatory` tinyint(4) NOT NULL,
  `keywords` varchar(200) NOT NULL,
  PRIMARY KEY (`disease`,`value`,`period`),
  CONSTRAINT `_disease_values_diseases` FOREIGN KEY (`disease`) REFERENCES `ses_diseases` (`disease`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_diseases`
--

DROP TABLE IF EXISTS `ses_diseases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_diseases` (
  `disease` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `keywords` varchar(200) NOT NULL,
  PRIMARY KEY (`disease`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_gateway_devices`
--

DROP TABLE IF EXISTS `ses_gateway_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_gateway_devices` (
  `gatewayId` varchar(255) NOT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `sdk` varchar(255) DEFAULT NULL,
  `versionName` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `battery` int(11) DEFAULT NULL,
  `power` int(11) DEFAULT NULL,
  `updateDate` datetime NOT NULL,
  `pollInterval` int(11) DEFAULT NULL,
  PRIMARY KEY (`gatewayId`),
  KEY `ses_gateway_devices_gatewayId_idx` (`gatewayId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_gateway_queue`
--

DROP TABLE IF EXISTS `ses_gateway_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_gateway_queue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `phoneNumber` varchar(255) NOT NULL,
  `message` varchar(1000) NOT NULL,
  `gatewayId` varchar(255) NOT NULL,
  `pending` bit(1) DEFAULT NULL,
  `sent` datetime DEFAULT NULL,
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `updateDate` datetime DEFAULT NULL,
  `failure` int(11) NOT NULL DEFAULT '0',
  `creationDay` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ses_gateway_queue_phoneNumber_idx` (`phoneNumber`),
  KEY `ses_gateway_queue_gatewayId_idx` (`gatewayId`),
  KEY `ses_gateway_queue_pending_idx` (`pending`),
  KEY `ses_gateway_queue_sent_idx` (`sent`),
  KEY `ses_gateway_queue_creationDay_idx` (`creationDay`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_incoming_sms`
--

DROP TABLE IF EXISTS `ses_incoming_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_incoming_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phoneNumber` varchar(255) NOT NULL,
  `message` varchar(1000) DEFAULT NULL,
  `gatewayId` varchar(255) NOT NULL,
  `creationDate` datetime NOT NULL,
  `FK_ContactId` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `pending` tinyint(1) DEFAULT NULL,
  `updateDate` datetime DEFAULT NULL,
  `comments` longtext,
  `creationDay` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4DA15C1794EF53A` (`FK_ContactId`),
  KEY `ses_incoming_sms_phoneNumber_idx` (`phoneNumber`),
  KEY `ses_incoming_sms_gatewayId_idx` (`gatewayId`),
  KEY `ses_incoming_sms_status_idx` (`status`),
  KEY `ses_incoming_sms_type_idx` (`type`),
  KEY `ses_incoming_sms_pending_idx` (`pending`),
  KEY `ses_incoming_sms_creationDay_idx` (`creationDay`),
  CONSTRAINT `FK_4DA15C1794EF53A` FOREIGN KEY (`FK_ContactId`) REFERENCES `sesdashboard_contacts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_nvc`
--

DROP TABLE IF EXISTS `ses_nvc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_nvc` (
  `collection` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `valueInteger` int(10) NOT NULL,
  `valueString` varchar(500) NOT NULL,
  PRIMARY KEY (`collection`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_recipients`
--

DROP TABLE IF EXISTS `ses_recipients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_recipients` (
  `path` varchar(255) NOT NULL,
  `pathRecipients` varchar(255) NOT NULL,
  `messageType` varchar(45) NOT NULL,
  PRIMARY KEY (`path`,`pathRecipients`,`messageType`),
  KEY `fk_recipients_sites_recipients_idx` (`pathRecipients`),
  CONSTRAINT `fk_recipients_sites` FOREIGN KEY (`path`) REFERENCES `frontline_group` (`path`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_recipients_sites_recipients` FOREIGN KEY (`pathRecipients`) REFERENCES `frontline_group` (`path`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_thresholds`
--

DROP TABLE IF EXISTS `ses_thresholds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_thresholds` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  `disease` varchar(100) NOT NULL,
  `period` varchar(45) NOT NULL,
  `weekNumber` int(11) DEFAULT NULL,
  `monthNumber` int(11) DEFAULT NULL,
  `year` int(11) NOT NULL,
  `maxValue` int(11) NOT NULL,
  `values` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_thresholds_diseases_idx` (`disease`),
  KEY `fk_thresholds_sites_idx` (`path`),
  CONSTRAINT `fk_thresholds_diseases` FOREIGN KEY (`disease`) REFERENCES `ses_diseases` (`disease`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_thresholds_sites` FOREIGN KEY (`path`) REFERENCES `frontline_group` (`path`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ses_version`
--

DROP TABLE IF EXISTS `ses_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ses_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(50) NOT NULL,
  `installationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Schema version';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_aggregatepartreport`
--

DROP TABLE IF EXISTS `sesdashboard_aggregatepartreport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_aggregatepartreport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `FK_PartReportOwnerId` int(11) NOT NULL,
  `FK_PartReportId` int(11) NOT NULL,
  `isDeleted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BF66BFA7F8AE1574` (`FK_PartReportOwnerId`),
  KEY `IDX_BF66BFA7CA1CD58A` (`FK_PartReportId`),
  CONSTRAINT `FK_BF66BFA7CA1CD58A` FOREIGN KEY (`FK_PartReportId`) REFERENCES `sesdashboard_partreport` (`id`),
  CONSTRAINT `FK_BF66BFA7F8AE1574` FOREIGN KEY (`FK_PartReportOwnerId`) REFERENCES `sesdashboard_partreport` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_alert`
--

DROP TABLE IF EXISTS `sesdashboard_alert`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_alert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contactName` varchar(255) NOT NULL,
  `contactPhoneNumber` varchar(255) NOT NULL,
  `FK_SiteId` int(11) DEFAULT NULL,
  `import_SiteName` varchar(255) NOT NULL,
  `receptionDate` datetime NOT NULL,
  `message` varchar(10000) NOT NULL,
  `isRead` tinyint(1) DEFAULT NULL,
  `isArchived` tinyint(1) DEFAULT NULL,
  `isDeleted` tinyint(1) DEFAULT NULL,
  `FK_SiteRelationShipId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DAE6416F35F629AE` (`FK_SiteId`),
  KEY `IDX_DAE6416FA1EA8FBE` (`FK_SiteRelationShipId`),
  CONSTRAINT `FK_DAE6416F35F629AE` FOREIGN KEY (`FK_SiteId`) REFERENCES `sesdashboard_sites` (`id`),
  CONSTRAINT `FK_DAE6416FA1EA8FBE` FOREIGN KEY (`FK_SiteRelationShipId`) REFERENCES `sesdashboard_sites_relationship` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `sesdashboard_contacts`
--

DROP TABLE IF EXISTS `sesdashboard_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phoneNumber` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `isDeleted` tinyint(1) DEFAULT NULL,
  `FK_SiteId` int(11) DEFAULT NULL,
  `imei` varchar(255) DEFAULT NULL,
  `imei2` varchar(255) DEFAULT NULL,
  `contactTypeId` int(11) DEFAULT NULL,
  `alertPreferredGateway` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DCF458A9E85E83E4` (`phoneNumber`),
  KEY `IDX_DCF458A935F629AE` (`FK_SiteId`),
  KEY `IDX_DCF458A93B4A6D35` (`contactTypeId`),
  CONSTRAINT `FK_DCF458A935F629AE` FOREIGN KEY (`FK_SiteId`) REFERENCES `sesdashboard_sites` (`id`),
  CONSTRAINT `FK_DCF458A93B4A6D35` FOREIGN KEY (`contactTypeId`) REFERENCES `sesdashboard_contacttype` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_contacttype`
--

DROP TABLE IF EXISTS `sesdashboard_contacttype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_contacttype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creationDate` datetime NOT NULL,
  `name` varchar(255) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `desc` varchar(255) DEFAULT NULL,
  `sendsReports` tinyint(1) NOT NULL,
  `useInIndicatorsCalculation` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_354AD93FAEA34913` (`reference`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_diseaseconstraints`
--

DROP TABLE IF EXISTS `sesdashboard_diseaseconstraints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_diseaseconstraints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `FK_DiseaseId` int(11) NOT NULL,
  `referenceValueFrom` varchar(100) NOT NULL,
  `operator` varchar(20) NOT NULL,
  `referenceValueTo` varchar(100) NOT NULL,
  `period` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5B98C86C283E239C` (`FK_DiseaseId`),
  CONSTRAINT `FK_5B98C86C283E239C` FOREIGN KEY (`FK_DiseaseId`) REFERENCES `sesdashboard_diseases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_diseases`
--

DROP TABLE IF EXISTS `sesdashboard_diseases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_diseases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `disease` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `keyWord` varchar(255) NOT NULL,
  `reportDataSourceId` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_18D64B9DF0B44D34` (`reportDataSourceId`),
  CONSTRAINT `FK_18D64B9DF0B44D34` FOREIGN KEY (`reportDataSourceId`) REFERENCES `sesdashboard_report_datasource` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_diseasevalues`
--

DROP TABLE IF EXISTS `sesdashboard_diseasevalues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_diseasevalues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `FK_DiseaseId` int(11) NOT NULL,
  `value` varchar(100) NOT NULL,
  `period` varchar(45) NOT NULL,
  `position` int(11) NOT NULL,
  `datatype` varchar(45) NOT NULL,
  `mandatory` tinyint(1) NOT NULL,
  `keyWord` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AE6D8871283E239C` (`FK_DiseaseId`),
  CONSTRAINT `FK_AE6D8871283E239C` FOREIGN KEY (`FK_DiseaseId`) REFERENCES `sesdashboard_diseases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_fullreport`
--

DROP TABLE IF EXISTS `sesdashboard_fullreport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_fullreport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period` varchar(255) NOT NULL,
  `FK_SiteId` int(11) DEFAULT NULL,
  `startDate` datetime NOT NULL,
  `weekNumber` int(11) DEFAULT NULL,
  `monthNumber` int(11) DEFAULT NULL,
  `year` int(11) NOT NULL,
  `import_SiteName` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `statusModifiedBy` varchar(255) DEFAULT NULL,
  `statusModifiedDate` datetime DEFAULT NULL,
  `aggregate` tinyint(1) DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `createdBy` varchar(255) DEFAULT NULL,
  `firstValidationDate` datetime DEFAULT NULL,
  `firstRejectionDate` datetime DEFAULT NULL,
  `FK_SiteRelationShipId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B3267335F629AE` (`FK_SiteId`),
  KEY `IDX_B32673A1EA8FBE` (`FK_SiteRelationShipId`),
  CONSTRAINT `FK_B3267335F629AE` FOREIGN KEY (`FK_SiteId`) REFERENCES `sesdashboard_sites` (`id`),
  CONSTRAINT `FK_B32673A1EA8FBE` FOREIGN KEY (`FK_SiteRelationShipId`) REFERENCES `sesdashboard_sites_relationship` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `sesdashboard_indicatordimdate`
--

DROP TABLE IF EXISTS `sesdashboard_indicatordimdate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_indicatordimdate` (
  `id` int(11) NOT NULL,
  `fullDate` date NOT NULL,
  `dateName` tinytext NOT NULL,
  `dateNameUS` tinytext NOT NULL,
  `dateNameEU` tinytext NOT NULL,
  `dayOfWeek` int(11) NOT NULL,
  `dayNameOfWeek` tinytext NOT NULL,
  `dayOfMonth` int(11) NOT NULL,
  `dayOfYear` int(11) NOT NULL,
  `weekdayWeekend` tinytext NOT NULL,
  `weekOfYear` int(11) NOT NULL,
  `epiWeekOfYear` int(11) DEFAULT NULL,
  `monthName` tinytext NOT NULL,
  `monthOfYear` int(11) NOT NULL,
  `isLastDayOfMonth` tinyint(1) NOT NULL,
  `calendarQuarter` int(11) NOT NULL,
  `calendarYear` int(11) NOT NULL,
  `calendarYearMonth` tinytext NOT NULL,
  `calendarYearQtr` tinytext NOT NULL,
  `fiscalMonthOfYear` int(11) NOT NULL,
  `fiscalQuarter` int(11) NOT NULL,
  `fiscalYear` int(11) NOT NULL,
  `fiscalYearMonth` tinytext NOT NULL,
  `fiscalYearQtr` tinytext NOT NULL,
  `weekYear` int(11) NOT NULL,
  `epiYear` int(11) DEFAULT NULL,
  `monthPeriodCode` varchar(7) DEFAULT NULL,
  `weekPeriodCode` varchar(7) DEFAULT NULL,
  `epiWeekPeriodCode` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sesdashboard_dimdate_idx` (`id`),
  KEY `IDX_C4E45D9AB62CE50F` (`monthPeriodCode`),
  KEY `IDX_C4E45D9AB209F2AB` (`weekPeriodCode`),
  KEY `IDX_C4E45D9AB58581E` (`epiWeekPeriodCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_indicatordimdatetype`
--

DROP TABLE IF EXISTS `sesdashboard_indicatordimdatetype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_indicatordimdatetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creationDate` datetime NOT NULL,
  `code` tinytext NOT NULL,
  `name` tinytext NOT NULL,
  `desc` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_lock`
--

DROP TABLE IF EXISTS `sesdashboard_lock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_lock` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `creationDate` datetime NOT NULL,
  `expire` double NOT NULL,
  PRIMARY KEY (`name`),
  UNIQUE KEY `UNIQ_EBBC09C55E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_log`
--

DROP TABLE IF EXISTS `sesdashboard_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creationDate` datetime NOT NULL,
  `source` longtext NOT NULL,
  `logLevelId` int(11) NOT NULL,
  `calculationSessionId` int(11) DEFAULT NULL,
  `message` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BA384E91FAF49BAC` (`logLevelId`),
  KEY `sesdashboard_log_calculationSessionId_idx` (`calculationSessionId`),
  CONSTRAINT `FK_BA384E91FAF49BAC` FOREIGN KEY (`logLevelId`) REFERENCES `sesdashboard_loglevel` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_loglevel`
--

DROP TABLE IF EXISTS `sesdashboard_loglevel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_loglevel` (
  `id` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `code` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_partreport`
--

DROP TABLE IF EXISTS `sesdashboard_partreport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_partreport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contactName` varchar(255) DEFAULT NULL,
  `contactPhoneNumber` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `FK_FullReportId` int(11) NOT NULL,
  `aggregate` tinyint(1) DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `createdBy` varchar(255) DEFAULT NULL,
  `statusModifiedBy` varchar(255) DEFAULT NULL,
  `statusModifiedDate` datetime DEFAULT NULL,
  `firstValidationDate` datetime DEFAULT NULL,
  `firstRejectionDate` datetime DEFAULT NULL,
  `androidReportId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E62F351119697BED` (`FK_FullReportId`),
  CONSTRAINT `FK_E62F351119697BED` FOREIGN KEY (`FK_FullReportId`) REFERENCES `sesdashboard_fullreport` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_permission`
--

DROP TABLE IF EXISTS `sesdashboard_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `ressource` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `dashboardRoleId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_93F36DA6DB4F0561` (`dashboardRoleId`),
  CONSTRAINT `FK_93F36DA6DB4F0561` FOREIGN KEY (`dashboardRoleId`) REFERENCES `sesdashboard_role` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_report`
--

DROP TABLE IF EXISTS `sesdashboard_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `disease` varchar(100) NOT NULL,
  `receptionDate` datetime NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `isArchived` tinyint(1) NOT NULL DEFAULT '0',
  `isDeleted` tinyint(1) NOT NULL DEFAULT '0',
  `FK_PartReportId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F220E75ACA1CD58A` (`FK_PartReportId`),
  CONSTRAINT `FK_F220E75ACA1CD58A` FOREIGN KEY (`FK_PartReportId`) REFERENCES `sesdashboard_partreport` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_report_datasource`
--

DROP TABLE IF EXISTS `sesdashboard_report_datasource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_report_datasource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `checkConfigurationConflict` tinyint(1) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_88D6AD3477153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_reportvalues`
--

DROP TABLE IF EXISTS `sesdashboard_reportvalues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_reportvalues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Key` varchar(45) NOT NULL,
  `Value` int(11) NOT NULL,
  `FK_ReportId` int(11) NOT NULL,
  `isArchived` tinyint(1) DEFAULT NULL,
  `isDeleted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1B347438B9AC9382` (`FK_ReportId`),
  CONSTRAINT `FK_1B347438B9AC9382` FOREIGN KEY (`FK_ReportId`) REFERENCES `sesdashboard_report` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_role`
--

DROP TABLE IF EXISTS `sesdashboard_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_sitealertrecipients`
--

DROP TABLE IF EXISTS `sesdashboard_sitealertrecipients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_sitealertrecipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipientSiteReference` varchar(255) NOT NULL,
  `FK_SiteId` int(11) DEFAULT NULL,
  `FK_RecipientSiteId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BC6790F935F629AE` (`FK_SiteId`),
  KEY `IDX_BC6790F99249768D` (`FK_RecipientSiteId`),
  CONSTRAINT `FK_BC6790F935F629AE` FOREIGN KEY (`FK_SiteId`) REFERENCES `sesdashboard_sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BC6790F99249768D` FOREIGN KEY (`FK_RecipientSiteId`) REFERENCES `sesdashboard_sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_sites`
--

DROP TABLE IF EXISTS `sesdashboard_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(255) NOT NULL,
  `weeklyReminderOverrunMinutes` int(11) NOT NULL,
  `monthlyReminderOverrunMinutes` int(11) NOT NULL,
  `weeklyTimelinessMinutes` int(11) NOT NULL,
  `monthlyTimelinessMinutes` int(11) NOT NULL,
  `locale` varchar(2) DEFAULT NULL,
  `timezone` varchar(100) DEFAULT NULL,
  `alertPreferredGateway` varchar(255) DEFAULT NULL,
  `reportDataSourceId` int(11) DEFAULT NULL,
  `cascadingAlert` TINYINT(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B0DA82C9AEA34913` (`reference`),
  KEY `IDX_B0DA82C9F0B44D34` (`reportDataSourceId`),
  CONSTRAINT `FK_B0DA82C9F0B44D34` FOREIGN KEY (`reportDataSourceId`) REFERENCES `sesdashboard_report_datasource` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_sites_relationship`
--

DROP TABLE IF EXISTS `sesdashboard_sites_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_sites_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `path` varchar(2000) DEFAULT NULL,
  `pathName` varchar(2000) DEFAULT NULL,
  `FK_ParentId` int(11) DEFAULT NULL,
  `FK_SiteId` int(11) DEFAULT NULL,
  `FK_DimDateFromId` int(11) DEFAULT NULL,
  `FK_DimDateToId` int(11) DEFAULT NULL,
  `FK_WeekDimDateFromId` int(11) DEFAULT NULL,
  `FK_WeekDimDateToId` int(11) DEFAULT NULL,
  `FK_MonthDimDateFromId` int(11) DEFAULT NULL,
  `FK_MonthDimDateToId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FD7522498AB7AD8` (`FK_DimDateFromId`),
  KEY `IDX_FD75224B7D37498` (`FK_DimDateToId`),
  KEY `IDX_FD752241271E161` (`FK_ParentId`),
  KEY `IDX_FD7522435F629AE` (`FK_SiteId`),
  KEY `IDX_FD75224CC0ECADE` (`FK_WeekDimDateFromId`),
  KEY `IDX_FD75224E1D72C52` (`FK_WeekDimDateToId`),
  KEY `IDX_FD75224E3CE75E9` (`FK_MonthDimDateFromId`),
  KEY `IDX_FD75224287C5CFB` (`FK_MonthDimDateToId`),
  CONSTRAINT `FK_FD752241271E161` FOREIGN KEY (`FK_ParentId`) REFERENCES `sesdashboard_sites` (`id`),
  CONSTRAINT `FK_FD75224287C5CFB` FOREIGN KEY (`FK_MonthDimDateToId`) REFERENCES `sesdashboard_indicatordimdate` (`id`),
  CONSTRAINT `FK_FD7522435F629AE` FOREIGN KEY (`FK_SiteId`) REFERENCES `sesdashboard_sites` (`id`),
  CONSTRAINT `FK_FD7522498AB7AD8` FOREIGN KEY (`FK_DimDateFromId`) REFERENCES `sesdashboard_indicatordimdate` (`id`),
  CONSTRAINT `FK_FD75224B7D37498` FOREIGN KEY (`FK_DimDateToId`) REFERENCES `sesdashboard_indicatordimdate` (`id`),
  CONSTRAINT `FK_FD75224CC0ECADE` FOREIGN KEY (`FK_WeekDimDateFromId`) REFERENCES `sesdashboard_indicatordimdate` (`id`),
  CONSTRAINT `FK_FD75224E1D72C52` FOREIGN KEY (`FK_WeekDimDateToId`) REFERENCES `sesdashboard_indicatordimdate` (`id`),
  CONSTRAINT `FK_FD75224E3CE75E9` FOREIGN KEY (`FK_MonthDimDateFromId`) REFERENCES `sesdashboard_indicatordimdate` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_thresholds`
--

DROP TABLE IF EXISTS `sesdashboard_thresholds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_thresholds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period` varchar(255) NOT NULL,
  `weekNumber` int(11) DEFAULT NULL,
  `monthNumber` int(11) DEFAULT NULL,
  `year` int(11) NOT NULL,
  `maximalValue` int(11) NOT NULL,
  `FK_SiteId` int(11) DEFAULT NULL,
  `FK_DiseaseId` int(11) DEFAULT NULL,
  `FK_DiseaseValueId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_71337C5C35F629AE` (`FK_SiteId`),
  KEY `IDX_71337C5C283E239C` (`FK_DiseaseId`),
  KEY `IDX_71337C5CF7FBB45C` (`FK_DiseaseValueId`),
  CONSTRAINT `FK_71337C5C283E239C` FOREIGN KEY (`FK_DiseaseId`) REFERENCES `sesdashboard_diseases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_71337C5C35F629AE` FOREIGN KEY (`FK_SiteId`) REFERENCES `sesdashboard_sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_71337C5CF7FBB45C` FOREIGN KEY (`FK_DiseaseValueId`) REFERENCES `sesdashboard_diseasevalues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_user`
--

DROP TABLE IF EXISTS `sesdashboard_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(180) NOT NULL,
  `username_canonical` varchar(180) NOT NULL,
  `email` varchar(180) NOT NULL,
  `email_canonical` varchar(180) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `confirmation_token` varchar(180) DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext NOT NULL COMMENT '(DC2Type:array)',
  `firstName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `rootSite` int(11) DEFAULT NULL,
  `locale` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E1A0448292FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_E1A04482A0D96FBF` (`email_canonical`),
  UNIQUE KEY `UNIQ_E1A04482C05FB297` (`confirmation_token`),
  KEY `IDX_E1A04482EB6CF7CE` (`rootSite`),
  CONSTRAINT `FK_E1A04482EB6CF7CE` FOREIGN KEY (`rootSite`) REFERENCES `sesdashboard_sites` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sesdashboard_users_sesdashboard_roles`
--

DROP TABLE IF EXISTS `sesdashboard_users_sesdashboard_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesdashboard_users_sesdashboard_roles` (
  `sesdashboarduser_id` int(11) NOT NULL,
  `sesdashboardrole_id` int(11) NOT NULL,
  PRIMARY KEY (`sesdashboarduser_id`,`sesdashboardrole_id`),
  KEY `IDX_1729DBCD6852A4C8` (`sesdashboarduser_id`),
  KEY `IDX_1729DBCD193F55F1` (`sesdashboardrole_id`),
  CONSTRAINT `FK_1729DBCD193F55F1` FOREIGN KEY (`sesdashboardrole_id`) REFERENCES `sesdashboard_role` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1729DBCD6852A4C8` FOREIGN KEY (`sesdashboarduser_id`) REFERENCES `sesdashboard_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `uvw_epidemiologic_weeks`
--

DROP TABLE IF EXISTS `uvw_epidemiologic_weeks`;
/*!50001 DROP VIEW IF EXISTS `uvw_epidemiologic_weeks`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `uvw_epidemiologic_weeks` AS SELECT 
 1 AS `date_id`,
 1 AS `firstdayOfWeek`,
 1 AS `calendarYear`,
 1 AS `epiWeekOfYear`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `uvw_sesdashboard_sites_relationship_current`
--

DROP TABLE IF EXISTS `uvw_sesdashboard_sites_relationship_current`;
/*!50001 DROP VIEW IF EXISTS `uvw_sesdashboard_sites_relationship_current`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `uvw_sesdashboard_sites_relationship_current` AS SELECT 
 1 AS `id`,
 1 AS `name`,
 1 AS `longitude`,
 1 AS `latitude`,
 1 AS `level`,
 1 AS `path`,
 1 AS `pathName`,
 1 AS `FK_ParentId`,
 1 AS `FK_SiteId`,
 1 AS `FK_DimDateFromId`,
 1 AS `FK_DimDateToId`,
 1 AS `FK_WeekDimDateFromId`,
 1 AS `FK_WeekDimDateToId`,
 1 AS `FK_MonthDimDateFromId`,
 1 AS `FK_MonthDimDateToId`,
 1 AS `isActiveDaily`,
 1 AS `isActiveWeekly`,
 1 AS `isActiveWeeklyEpidemiologic`,
 1 AS `isActiveMonthly`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `uvw_sesdashboard_sites_relationship_last_changes`
--

DROP TABLE IF EXISTS `uvw_sesdashboard_sites_relationship_last_changes`;
/*!50001 DROP VIEW IF EXISTS `uvw_sesdashboard_sites_relationship_last_changes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `uvw_sesdashboard_sites_relationship_last_changes` AS SELECT 
 1 AS `FK_SiteId`,
 1 AS `FK_DimDateFromId`*/;
SET character_set_client = @saved_cs_client;

-- Dump completed on 2018-08-22 15:04:56
