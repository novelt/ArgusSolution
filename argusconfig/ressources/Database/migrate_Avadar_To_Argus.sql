DELIMITER $$

DROP PROCEDURE IF EXISTS migrate_Avadar_To_Argus$$
CREATE PROCEDURE migrate_Avadar_To_Argus()
BEGIN

	-- Remove table argus_alert_event
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'argus_alert_event') THEN
		DROP TABLE argus_alert_event;
	END IF;

	-- Remove table argus_alert_investigation
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'argus_alert_investigation') THEN
		DROP TABLE argus_alert_investigation;
	END IF;

	-- Remove table argus_event
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'argus_event') THEN
		DROP TABLE argus_event;
	END IF;

	-- Remove table argus_eventfield
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'argus_eventfield') THEN
		DROP TABLE argus_eventfield;
	END IF;

	-- Remove table argus_eventtype
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'argus_eventtype') THEN
		DROP TABLE argus_eventtype;
	END IF;

	-- Remove table multipart_message
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'multipart_message') THEN
		DROP TABLE multipart_message;
	END IF;

	-- Remove table sesdashboard_alertdata
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_alertdata') THEN
		DROP TABLE sesdashboard_alertdata;
	END IF;

	-- Remove table sesdashboard_casedata
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_casedata') THEN
		DROP TABLE sesdashboard_casedata;
	END IF;

	-- Remove table sesdashboard_eventdatacolumngrouptabmembership
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_eventdatacolumngrouptabmembership') THEN
		DROP TABLE sesdashboard_eventdatacolumngrouptabmembership;
	END IF;

	-- Remove table sesdashboard_eventdatacolumngroupmembership
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_eventdatacolumngroupmembership') THEN
		DROP TABLE sesdashboard_eventdatacolumngroupmembership;
	END IF;

	-- Remove table sesdashboard_eventdatamaplegendtabmembership
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_eventdatamaplegendtabmembership') THEN
		DROP TABLE sesdashboard_eventdatamaplegendtabmembership;
	END IF;

	-- Remove table sesdashboard_eventdatacolumn
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_eventdatacolumn') THEN
		DROP TABLE sesdashboard_eventdatacolumn;
	END IF;

	-- Remove table sesdashboard_eventdatacolumngroup
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_eventdatacolumngroup') THEN
		DROP TABLE sesdashboard_eventdatacolumngroup;
	END IF;

	-- Remove table sesdashboard_eventdatacolumntab
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_eventdatacolumntab') THEN
		DROP TABLE sesdashboard_eventdatacolumntab;
	END IF;

	-- Remove table sesdashboard_eventdatamaplegend
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_eventdatamaplegend') THEN
		DROP TABLE sesdashboard_eventdatamaplegend;
	END IF;

	-- Remove table sesdashboard_eventdatatype
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_eventdatatype') THEN
		DROP TABLE sesdashboard_eventdatatype;
	END IF;


    -- INDICATORS FIRST ?

	-- Remove table sesdashboard_indicatorscopegroupmembership
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicatorscopegroupmembership') THEN
		DROP TABLE sesdashboard_indicatorscopegroupmembership;
	END IF;

	-- Remove table sesdashboard_indicatorscopegrouptabmembership
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicatorscopegrouptabmembership') THEN
		DROP TABLE sesdashboard_indicatorscopegrouptabmembership;
	END IF;

	-- Remove table sesdashboard_indicatorscopethreshold
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicatorscopethreshold') THEN
		DROP TABLE sesdashboard_indicatorscopethreshold;
	END IF;

	-- Remove table sesdashboard_indicatorscopetab
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicatorscopetab') THEN
		DROP TABLE sesdashboard_indicatorscopetab;
	END IF;

    -- Remove table sesdashboard_indicatorscopegroup
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicatorscopegroup') THEN
		DROP TABLE sesdashboard_indicatorscopegroup;
	END IF;

	 -- Remove table sesdashboard_indicatorscopedependency
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicatorscopedependency') THEN
		DROP TABLE sesdashboard_indicatorscopedependency;
	END IF;

	 -- Remove table sesdashboard_indicatorscope
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicatorscope') THEN
		DROP TABLE sesdashboard_indicatorscope;
	END IF;

	 -- Remove table sesdashboard_indicatordatatype
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicatordatatype') THEN
		DROP TABLE sesdashboard_indicatordatatype;
	END IF;

    -- Remove table sesdashboard_indicatordata
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicatordata') THEN
		DROP TABLE sesdashboard_indicatordata;
	END IF;

    -- Remove table sesdashboard_indicators_sesdashboard_roles
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicators_sesdashboard_roles') THEN
		DROP TABLE sesdashboard_indicators_sesdashboard_roles;
	END IF;

	-- Remove table sesdashboard_indicator
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_indicator') THEN
		DROP TABLE sesdashboard_indicator;
	END IF;

    -- SURVAPP

    -- Remove table survapp_afp_report
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'survapp_afp_report') THEN
		DROP TABLE survapp_afp_report;
	END IF;

	 -- Remove table survapp_location
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'survapp_location') THEN
		DROP TABLE survapp_location;
	END IF;

	 -- Remove table sesdashboard_odkinvestigation
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_odkinvestigation') THEN
		DROP TABLE sesdashboard_odkinvestigation;
	END IF;

    -- TASK

    -- Remove table sesdashboard_task
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sesdashboard_task') THEN
		DROP TABLE sesdashboard_task;
	END IF;


    -- sesdashboard_role
	IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sesdashboard_role' AND COLUMN_NAME = 'angularModules') THEN
		ALTER TABLE sesdashboard_role DROP angularModules;
	END IF;

	IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sesdashboard_role' AND COLUMN_NAME = 'authorizedConfidentialCasesData') THEN
		ALTER TABLE sesdashboard_role DROP authorizedConfidentialCasesData;
	END IF;

    IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sesdashboard_role' AND COLUMN_NAME = 'authorizedCasesDataExport') THEN
		ALTER TABLE sesdashboard_role DROP authorizedCasesDataExport;
	END IF;

	IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sesdashboard_role' AND COLUMN_NAME = 'authorizedIndicatorsDataExport') THEN
		ALTER TABLE sesdashboard_role DROP authorizedIndicatorsDataExport;
	END IF;

	IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sesdashboard_role' AND COLUMN_NAME = 'authorizedCasesDataEdit') THEN
		ALTER TABLE sesdashboard_role DROP authorizedCasesDataEdit;
	END IF;

	IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sesdashboard_role' AND COLUMN_NAME = 'authorizedCasesDataSoftDelete') THEN
		ALTER TABLE sesdashboard_role DROP authorizedCasesDataSoftDelete;
	END IF;

    TRUNCATE TABLE ses_version ;
    INSERT INTO ses_version (id, version, installationDate) values (1, '1.0.0', now());

END $$

CALL migrate_Avadar_To_Argus() $$
DROP PROCEDURE IF EXISTS migrate_Avadar_To_Argus $$

DELIMITER ;

-- PROCEDURES

DROP PROCEDURE IF EXISTS `usp_entity_duplicate`;
DROP PROCEDURE IF EXISTS `usp_sesdahboard_sumIndicators`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_alertdata_refresh`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_calculateIndicators`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_casedata_refresh`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_cleanIndicators`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_getIndicatorsData`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorCalculator_aggregate`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_CompletenessLeafReports`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_CompletenessLeafReportsWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_DiseaseConfirmationRate`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_DiseaseConfirmationRateWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_ExpectedLeafReports`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_ExpectedLeafReportsWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_InvestigationRate`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_InvestigationRateWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_NumberOfDiseaseValues`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_NumberOfDiseaseValuesWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_NumberOfEvents`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_NumberOfEventsWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_NumberOfZeroEvents`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_NumberOfZeroEventsWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_OnTimeReceivedLeafReports`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_OnTimeReceivedLeafReportsWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_ReceivedLeafReports`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_ReceivedLeafReportsWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_TimelinessLeafReports`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorcalculator_TimelinessLeafReportsWE`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_IndicatorScopes_buildAndLoadDependencies`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_IndicatorScopes_buildStack`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_IndicatorScopes_getLeafScopes`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_IndicatorScopes_getOrderedScopes`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_IndicatorScopes_getScopesWithSameDimensions`;
DROP PROCEDURE IF EXISTS `usp_sesdashboard_IndicatorScopes_loadStack`;
DROP PROCEDURE IF EXISTS `usp_survapp_afp_report_insert`;

-- VIEWS

DROP VIEW IF EXISTS `uvw_indicators_data`;
DROP VIEW IF EXISTS `uvw_sesdashboard_alerts_data`;
DROP VIEW IF EXISTS `uvw_sesdashboard_alerts_indicators`;
DROP VIEW IF EXISTS `uvw_sesdashboard_cases`;
DROP VIEW IF EXISTS `uvw_sesdashboard_cases_data`;
DROP VIEW IF EXISTS `uvw_sesdashboard_cases_indicators`;
DROP VIEW IF EXISTS `uvw_sesdashboard_events`;
DROP VIEW IF EXISTS `uvw_survapp_afp_reports`;