<?php

	// List of very common includes
	require_once(__DIR__."/../config/globals.php");
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
	{
		require_once(__DIR__ . '/../config/globals-win.php');
	}
	else
	{
		require_once(__DIR__ . '/../config/globals-linux.php');
	}

	require_once("language_functions.php");
	require_once("logging_functions.php");
	require_once("config_functions.php");
	require_once("mysql_functions.php");

	// Setup language
	language_set($config["language"]);
    putenv("LC_ALL=".$config["language"]);
	setlocale(LC_ALL, $config["language"]);

	$domain = "messages";
	bindtextdomain($domain, "locale");
	textdomain($domain);
?>
