<?php

$config["path_backup"]         = "C:\\Backup\\SES\\";

$config['php_os'] = 'win';

//Xampp Installation paths
$config["xampp_path"]          = "C:\\xampp" . DIRECTORY_SEPARATOR;
// Php ini
$config["php_conf_path"]       = $config["xampp_path"]."php\\php.ini";
// MySql ini
$config["mysql_conf_path"]     = $config["xampp_path"]."mysql\\bin\\my.ini";
// MySql exe
$config["mysql_exe_path"]      = $config["xampp_path"]."mysql\\bin\\mysql.exe";
// MySql dump exe
$config["mysql_dump_exe_path"] = $config["xampp_path"]."mysql\\bin\\mysqldump.exe";
// Apache conf
$config["apache_conf_path"]    = $config["xampp_path"]."apache\\conf\\httpd.conf";
// Xampp version
$config["xampp_version_path"]  = $config["xampp_path"]."htdocs\\xampp\\.version";
