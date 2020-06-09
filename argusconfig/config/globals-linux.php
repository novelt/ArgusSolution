<?php

$config["path_backup"]      = "/var/www/html/backup/";

$config['php_os'] = 'linux';

// Linux etc path
$config['etc_path']         = '/etc';
// PHP version
$config['php_version']      = '7.2';
// PHP ini
$config['php_conf_path']    = $config['etc_path'] . '/php/' . $config['php_version'] . '/apache2/php.ini';
// MySQL conf
$config['mysql_conf_path']  = $config['etc_path'] . '/mysql/mysql.cnf';
// MySql dump exe
$config["mysql_dump_exe_path"] = 'mysqldump';
// MySQL command
$config['mysql_exe_path']   = 'mysql';
// Apache conf
$config['apache_conf_path'] = $config['etc_path'] . '/apache2/apache2.conf';
