<?php

include_once 'header.php';

$view->set_filenames([
    'msnou' => 'msnou.html'
]);

foreach(Defs::$table_head['ms_nou'] as $column_name=>$column_field) {
    $view->assign_block_vars("column", [
        'COLUMN' => $column_name
    ]);
}

$rows = $app->getEntries('cfr');

foreach($rows as $row) {
    $check_ms_vechi = $app->checkMsVchi($row);

    $css = '';

    if($check_ms_vechi['idEntry'] == '') {
        $css = 'table-danger';
    }

    $view->assign_block_vars("row", [
        'ID' => $row['idEntry'],

        'DENUMIRE' => $row['persoana_juridica'],
        'CUI' => $row['cui'],
        'LOC_SS' => $row['localitate_sediu_social'],
        'ADRESA_SS' => $row['adresa_sediu_social'],
        'JUDT_SS' => 'Tulcea',
        'DENUMIRE_PL' => $row['denumire_pl'],
        'TIP' => $row['tip'],
        'LOC_PL' => $row['localitate_pl'],
        'URBAN_RURAL' => $row['urban_rural'],
        'ADRESA_PL' => $row['adresa_pl'],
        'JUDET_PL' => 'Tulcea',
        'NR_AUT' => $row['nr_autorizatie'],
        'DATA_AUT' => $row['data_autorizare'],
        'RECEPTURA' => '',
        'MENTIUNI' => '',
        'FARM_SEF' => '',
        'OBSERVATII' => '',
        'TEL_PL' => '',
        'EMAIL_PL' => '',
        'FOND_COMERT' => '',
        'MS' => $check_ms_vechi['idEntry'] != '' ? 'DA' : 'NU',
        'CFR' => 'DA',

        'CSS' => $css,

        'STREET_NO' => $row['street_no']
    ]);
}

$rows = $app->getEntries('ms_vechi');

foreach($rows as $row) {
    $check_cfr = $app->checkCFR($row);

    if($check_cfr['idEntry'] == '') {
        $view->assign_block_vars("row", [
            'ID' => $row['idEntry'],

            'DENUMIRE' => $row['denumire'].' '.$row['in_structura'],
            'CUI' => '',
            'LOC_SS' => $row['localitate'],
            'ADRESA_SS' => $row['adresa'],
            'JUDT_SS' => $row['jud_sec'],
            'DENUMIRE_PL' => '',
            'TIP' => $row['tip'],
            'LOC_PL' => $row['localitate'],
            'URBAN_RURAL' => $row['urban_rural'],
            'ADRESA_PL' => $row['adresa'],
            'JUDET_PL' => $row['jud_sec'],
            'NR_AUT' => $row['aut'],
            'DATA_AUT' => $row['data_infiintare'],
            'RECEPTURA' => '',
            'MENTIUNI' => '',
            'FARM_SEF' => '',
            'OBSERVATII' => $row['observatii'],
            'TEL_PL' => '',
            'EMAIL_PL' => '',
            'FOND_COMERT' => $row['fond_comert'],
            'MS' => 'DA',
            'CFR' => 'NU',

            'CSS' => 'table-warning',

            'STREET_NO' => $row['street_no']
        ]);
    }
}

$rows = $app->getEntries('ms_vechi');

foreach($rows as $row) {
    $check_closed = $app->checkClosed($row);

    if($check_closed['idEntry'] != '') {
        $view->assign_block_vars("row", [
            'ID' => $row['idEntry'],

            'DENUMIRE' => $row['denumire'].' '.$row['in_structura'],
            'CUI' => '',
            'LOC_SS' => $row['localitate'],
            'ADRESA_SS' => $row['adresa'],
            'JUDT_SS' => $row['jud_sec'],
            'DENUMIRE_PL' => '',
            'TIP' => $row['tip'],
            'LOC_PL' => $row['localitate'],
            'URBAN_RURAL' => $row['urban_rural'],
            'ADRESA_PL' => $row['adresa'],
            'JUDET_PL' => $row['jud_sec'],
            'NR_AUT' => $row['aut'],
            'DATA_AUT' => $row['data_infiintare'],
            'RECEPTURA' => '',
            'MENTIUNI' => '',
            'FARM_SEF' => '',
            'OBSERVATII' => $row['observatii'],
            'TEL_PL' => '',
            'EMAIL_PL' => '',
            'FOND_COMERT' => $row['fond_comert'],
            'MS' => 'DA',
            'CFR' => 'NU',

            'CSS' => 'table-info',

            'STREET_NO' => $row['street_no']
        ]);
    }
}

$view->assign_vars([
    '_APP_NAME' => _APP_NAME,
    '_URL' => _SITE_URL,
    '_TPL' => _SITE_URL._TPL
]);

$view->pparse('msnou');

include_once 'footer.php';