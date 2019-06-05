-- INSERT SitesRoot default site
INSERT INTO `sesdashboard_sites` 	(`reference`, `weeklyReminderOverrunMinutes`, `monthlyReminderOverrunMinutes`, 	`weeklyTimelinessMinutes`, `monthlyTimelinessMinutes`)
VALUES 														('SitesRoot', 0, 															0, 																10080, 											70560);

INSERT INTO `sesdashboard_sites_relationship` (`id`, `name`, 				`longitude`, `latitude`, 	`level`, 	`path`, 			`pathName`, 		`FK_ParentId`, 	`FK_SiteId`)
VALUES 																				(1, 		'SitesRoot', 	NULL, 				NULL, 			0, 				'|SitesRoot', '|SitesRoot',		NULL, 					(SELECT id from `sesdashboard_sites` WHERE reference = 'SitesRoot' ));

-- INSERT Admin User
INSERT INTO `sesdashboard_user` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `firstName`, `lastName`, `rootSite`, `locale`) VALUES ('0', 'ArgusAdmin', 'argusadmin', 'ArgusAdmin@novel-t.ch', 'argusadmin@novel-t.ch', '1', 'av0usrqt3vccwscs08sog80s48gccs8', '$2y$13$av0usrqt3vccwscs08soguuPCklIoikvExfxYI0.hbWlC1Qub3xPi', '2016-05-31 10:23:09', NULL, NULL, 'a:1:{i:0;s:10:"ROLE_ADMIN";}', NULL, NULL, NULL, NULL);

-- INSERT sesdashboard_indicatordimdatetype
INSERT INTO `sesdashboard_indicatordimdatetype` (`id`, `creationDate`, `code`, `name`, `desc`) VALUES
	(13, '2017-11-09 15:14:22', 'Daily', 'Daily', 'Daily period'),
	(14, '2017-11-09 15:14:22', 'Weekly', 'Weekly', 'Weekly period'),
	(15, '2017-11-09 15:14:22', 'WeeklyEpidemiologic', 'Weekly', 'Epidemiologic week period'),
	(16, '2017-11-09 15:14:22', 'Monthly', 'Monthly', 'Monthly period'),
	(17, '2017-11-09 15:14:22', 'Yearly', 'Yearly', 'Yearly period'),
	(18, '2017-11-09 15:14:22', 'Custom', 'Custom', 'Custom period');

-- INSERT sesdashboard_loglevel
INSERT INTO `sesdashboard_loglevel` (`id`, `creationDate`, `code`) VALUES
	(100, '2017-11-08 10:16:28', 'DEBUG'),
	(200, '2017-11-08 10:16:28', 'INFO'),
	(250, '2017-11-08 10:16:28', 'NOTICE'),
	(300, '2017-11-08 10:16:28', 'WARNING'),
	(400, '2017-11-08 10:16:28', 'ERROR'),
	(500, '2017-11-08 10:16:28', 'CRITICAL'),
	(550, '2017-11-08 10:16:28', 'ALERT'),
	(600, '2017-11-08 10:16:28', 'EMERGENCY');


/***************************************************************/
/************************ SCHEMA VERSION ***********************/
/***************************************************************/

-- INSERT current version of app
INSERT INTO `ses_version` (`version`,`installationDate`) VALUES ('1.0.0', NOW());
