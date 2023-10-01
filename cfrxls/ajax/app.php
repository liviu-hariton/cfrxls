<?php

set_include_path(get_include_path() . PATH_SEPARATOR . str_replace("/ajax", "", realpath('.')));
require_once 'configure/configure.php';

if(isset($_POST['action'])) {
    switch($_POST['action']) {
        case "update_street_no":
            echo $app->updateStreetNo($_POST['id'], $_POST['new_value'], $_POST['table']);
            break;
    }
}