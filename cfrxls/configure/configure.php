<?php

session_start();

define('_SID', session_id());

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

set_time_limit(0);

$dev_ips = [
    '136.255.134.150',
    '5.14.117.87'
];

error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT ^ E_DEPRECATED ^ E_WARNING);

define('_TIME_ZONE', 'Europe/Bucharest');

date_default_timezone_set(_TIME_ZONE);
setlocale(LC_ALL, 'ro_RO.UTF8');
ini_set('date.timezone', _TIME_ZONE);

ini_set('session.gc_maxlifetime', 8 * 30 * 60);

define('_ROOT', dirname(__DIR__).'/');

define('_MEDIA_ROOT', _ROOT.'media/');
define('_UPLOAD_ROOT', _MEDIA_ROOT.'upload/');
define('_DOWNLOAD_ROOT', _MEDIA_ROOT.'download/');

define('DIR_WRITE_MODE', '777');

require_once 'access.php';

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('.').'/library/helpers');

define('_SQL_TIME', false);

require_once 'library/helpers/helpers.php';

define('_HTTP_PROTOCOL', 'https');

define('_SITE_URL', _HTTP_PROTOCOL.'://'._MAIN_DOMAIN.'/cfrxls/');

define('_TPL', 'layouts/'._FRONT_TPL.'/');

require_once 'library/application/application.php';