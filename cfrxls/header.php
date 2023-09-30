<?php

require_once 'configure/configure.php';

if(isset($_POST['go-upload'])) {
    $app->upload();
}

if(isset($_GET['resetupload'])) {
    $app->reset();
}

$view->set_filenames([
    'header' => 'header.html'
]);

if(isset($_GET['type'])) {
    $view->assign_block_vars("back", []);
}

$view->assign_vars([
    '_APP_NAME' => _APP_NAME,
    '_URL' => _SITE_URL,
    '_TPL' => _SITE_URL._TPL
]);

$view->pparse('header');