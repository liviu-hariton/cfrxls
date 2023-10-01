<?php

include_once 'header.php';

$view->set_filenames([
    'index' => 'index.html'
]);

if(isset($_GET['uploadsuccess'])) {
    $view->assign_block_vars("uploadsuccess", []);
}

if(isset($_GET['resetsuccess'])) {
    $view->assign_block_vars("resetsuccess", []);
}

$loaded_check = [];

if(!file_exists(_UPLOAD_ROOT._SID)) {
    $view->assign_block_vars("load_ms_vechi", []);
    /*$view->assign_block_vars("load_ms_nou", []);*/
    $view->assign_block_vars("load_cfr", []);
    $view->assign_block_vars("load_inchise", []);
} else {
    if(!file_exists(_UPLOAD_ROOT._SID.'/ms_vechi.xls')) {
        $view->assign_block_vars("load_ms_vechi", []);
    } else {
        $view->assign_block_vars("ms_vechi", [
            'COUNT' => $app->countEntries('ms_vechi')
        ]);

        $loaded_check[] = 'ms_vechi';
    }

    /*if(!file_exists(_UPLOAD_ROOT._SID.'/ms_nou.xls')) {
        $view->assign_block_vars("load_ms_nou", []);
    } else {
        $view->assign_block_vars("ms_nou", [
            'COUNT' => $app->countEntries('ms_nou')
        ]);

        $loaded_check[] = 'ms_nou';
    }*/

    if(!file_exists(_UPLOAD_ROOT._SID.'/cfr.xls')) {
        $view->assign_block_vars("load_cfr", []);
    } else {
        $view->assign_block_vars("cfr", [
            'COUNT' => $app->countEntries('cfr')
        ]);

        $loaded_check[] = 'cfr';
    }

    if(!file_exists(_UPLOAD_ROOT._SID.'/inchise.xls')) {
        $view->assign_block_vars("load_inchise", []);
    } else {
        $view->assign_block_vars("inchise", [
            'COUNT' => $app->countEntries('inchise')
        ]);

        $loaded_check[] = 'inchise';
    }

    $view->assign_block_vars("reset", []);
}

if(count($loaded_check) == 3) {
    $view->assign_block_vars("generate", []);
} else {
    $view->assign_block_vars("load", []);
    $view->assign_block_vars("no_generate", []);
}

$view->assign_vars([
    '_APP_NAME' => _APP_NAME,
    '_URL' => _SITE_URL,
    '_TPL' => _SITE_URL._TPL
]);

$view->pparse('index');

include_once 'footer.php';