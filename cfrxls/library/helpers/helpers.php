<?php

require_once 'db/mysql.php';
$db = new Db(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASS, _MYSQL_NAME);

$settings_resource = $db->sqlQuery("select * from "._MYSQL_PREFIX."settings");
while($settings_data = $db->sqlFetchrow($settings_resource)) {
    define('_'.strtoupper($settings_data['key']), $settings_data['value']);
}

require_once 'view/view.php';
$view = new View;

require_once 'etc/etc.php';
$etc = new Etc;

require_once 'csvread/CsvInterface.class.php';
require_once 'csvread/Csv.class.php';

require_once 'phpspreadsheet/autoload.php';