<?php

include_once 'header.php';

$view->set_filenames([
    'show' => 'show.html'
]);

foreach(Defs::$table_head[$_GET['type']] as $column_name=>$column_field) {
    $view->assign_block_vars("column", [
        'COLUMN' => $column_name
    ]);
}

$rows = $app->getEntries($_GET['type']);

foreach($rows as $row) {
    $view->assign_block_vars("row", [
        'ID' => $row['idEntry'],
        'TABLE' => $_GET['type']
    ]);

    $src_data = [];

    foreach(Defs::$table_head[$_GET['type']] as $column_name=>$column_field) {
        $view->assign_block_vars("row.value", [
            'VALUE' => $row[$column_field]
        ]);

        if(in_array($column_field, Defs::$src_fields)) {
            $src_data[] = $row[$column_field];
        }

        if(in_array($column_field, Defs::$street_fields) && $_GET['type'] != 'inchise') {
            $view->assign_block_vars("row.value.street_no", [
                'STREET_NO' => $row['street_no']
            ]);
        }
    }

    $view->assign_block_vars("row.src_data", [
        'SRC_DATA' => implode(' ', $src_data)
    ]);
}

$view->assign_vars([
    'TITLE' => $app->setPageTitle($_GET['type']),

    '_APP_NAME' => _APP_NAME,
    '_URL' => _SITE_URL,
    '_TPL' => _SITE_URL._TPL
]);

$view->pparse('show');

include_once 'footer.php';