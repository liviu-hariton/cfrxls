<?php

$view->set_filenames([
    'footer' => 'footer.html'
]);

$view->assign_vars([
    '_SID' => _SID,

    '_APP_NAME' => _APP_NAME,
    '_URL' => _SITE_URL,
    '_TPL' => _SITE_URL._TPL
]);

$view->pparse('footer');