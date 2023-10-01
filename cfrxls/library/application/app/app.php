<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

#[AllowDynamicProperties] class App
{
    public function __construct()
    {
        global $db, $etc, $view;

        $this->db = $db;
        $this->etc = $etc;
        $this->view = $view;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function upload(): void
    {
        $this->createSessionFolder();

        foreach(Defs::$files as $file) {
            if(!empty($_FILES[$file]['name']) && $_FILES[$file]['error'] == '0') {
                move_uploaded_file($_FILES[$file]['tmp_name'], _UPLOAD_ROOT._SID.'/'.$file.'.xls');

                $this->loadFileData(_UPLOAD_ROOT._SID.'/'.$file.'.xls', $file);
            }
        }

        $this->etc->redirect(_SITE_URL.'?uploadsuccess');
    }

    private function setNameCheck($input): string
    {
        $input = strtolower($input);

        $src = [
            'sc', 's.c.', 's.r.l.', 'srl-d', 'srl'
        ];

        $rpl = [
            '', '', '', '', ''
        ];

        $input = str_replace($src, $rpl, $input);

        $input = trim($input);

        return $input;
    }

    private function parseStreet($input): array
    {
        $input = strtolower($input);

        $src = [
            'str.', 'str', 'nr.', 'nr', ','
        ];

        $rpl = [
            '', '', '', '', ' '
        ];

        $input = str_replace($src, $rpl, $input);

        $input = trim($input);

        return explode(" ", $input);
    }

    private function setStreetName($input)
    {
        $parts = $this->parseStreet($input);

        return $parts[0];
    }

    private function setStreetNo($input)
    {
        $parts = $this->parseStreet($input);

        return $parts[1];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function loadFileData($file_path, $type): void
    {
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($file_path);

        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        unset($sheetData[1]);

        $file_fields = $type.'_fields';

        foreach($sheetData as $row) {
            $db_data = [];

            foreach($row as $row_column=>$row_value) {
                if(Defs::$$file_fields[$row_column] != '') {
                    $db_data[Defs::$$file_fields[$row_column]] = $row_value;
                }

                $db_data['session'] = _SID;

                foreach(Defs::$namecheck_keys as $name_key) {
                    if(isset($db_data[$name_key])) {
                        $name_check = $this->setNameCheck($db_data[$name_key]);
                        break;
                    }
                }

                $db_data['name_check'] = $name_check;
                $db_data['name_hash'] = md5($name_check);

                if($type != 'inchise') {
                    foreach(Defs::$address_keys as $address_key) {
                        if(isset($db_data[$address_key])) {
                            $street_name = $this->setStreetName($db_data[$address_key]);
                            $street_no = $this->setStreetNo($db_data[$address_key]);
                            break;
                        }
                    }

                    $db_data['street_no'] = $street_name.' '.$street_no;
                }

            }

            $this->db->sqlInsert(
                _MYSQL_PREFIX.$type, $db_data
            );
        }
    }

    public function reset(): void
    {
        if(file_exists(_UPLOAD_ROOT._SID)) {
            foreach(Defs::$files as $file) {
                unlink(_UPLOAD_ROOT._SID.'/'.$file.'.xls');
            }

            foreach(Defs::$tables as $table) {
                $this->db->sqlQuery(
                    "delete from "._MYSQL_PREFIX.$table." 
                    where `session` like '".$this->db->sqlCleanInput(_SID)."'"
                );
            }
        }

        $this->etc->redirect(_SITE_URL.'?resetsuccess');
    }

    private function createSessionFolder(): void
    {
        if(!file_exists(_UPLOAD_ROOT._SID)) {
            mkdir(_UPLOAD_ROOT._SID);
        }
    }

    public function countEntries($table)
    {
        $data = $this->db->sqlFetchAssoc(
            $this->db->sqlQuery(
                "select count(*) as total from "._MYSQL_PREFIX.$table." 
                where `session` like '".$this->db->sqlCleanInput(_SID)."'"
            )
        );

        return $data['total'] ?? '0';
    }

    public function getEntries($table): array
    {
        $data = [];

        $resource = $this->db->sqlQuery(
            "select * from "._MYSQL_PREFIX.$table." 
            where `session` like '".$this->db->sqlCleanInput(_SID)."' 
            order by `idEntry` asc"
        );

        while($item = $this->db->sqlFetchAssoc($resource)) {
            $data[] = $item;
        }

        return $data;
    }

    public function setPageTitle($input): string
    {
        $input = str_replace("_", " ", $input);

        return ucwords(strtolower($input));
    }

    public function updateStreetNo($id, $value, $table): array|string|null
    {
        $new_value = trim(strtolower($value));

        $new_value = preg_replace('/\s+/', ' ', $new_value);

        $data = [
            'street_no' => $new_value
        ];

        $this->db->sqlUpdate(
            _MYSQL_PREFIX.$table, $data, [], "`idEntry` = ".$this->db->sqlCleanInput($id).""
        );

        return $new_value;
    }

    public function checkMsVchi($data): false|array|null
    {
        $data = $this->db->sqlFetchAssoc(
            $this->db->sqlQuery(
                "select * from "._MYSQL_PREFIX."ms_vechi 
                where 
                    `name_check` like '%".$this->db->sqlCleanInput($data['name_check'])."%' and 
                    `street_no` like '%".$this->db->sqlCleanInput($data['street_no'])."%'"
            )
        );

        return $data;
    }

    public function checkCFR($data): false|array|null
    {
        $data = $this->db->sqlFetchAssoc(
            $this->db->sqlQuery(
                "select * from "._MYSQL_PREFIX."cfr 
                where 
                    `name_check` like '%".$this->db->sqlCleanInput($data['name_check'])."%' and 
                    `street_no` like '%".$this->db->sqlCleanInput($data['street_no'])."%'"
            )
        );

        return $data;
    }

    public function checkClosed($data): false|array|null
    {
        $data = $this->db->sqlFetchAssoc(
            $this->db->sqlQuery(
                "select * from "._MYSQL_PREFIX."inchise 
                where 
                    `name_check` like '%".$this->db->sqlCleanInput($data['name_check'])."%'"
            )
        );

        return $data;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function generateXls()
    {
        $spreadsheet = new Spreadsheet();

        /**
         * MS nou
         */
        $sheet_count = 0;

        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('A1', 'Denumire societate');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('B1', 'CUI');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('C1', 'Localitate Sediu Social');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('D1', 'Adresa Sediu Social');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('E1', 'Judet sediu social');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('F1', '"Denumire PL unitate farmaceutica"');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('G1', 'Tip UNITATE');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('H1', 'Localitate unit farm / PL');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('I1', 'Urban/Rural');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('J1', 'Adresa Farmacie');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('K1', 'Judet punct lucru');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('L1', 'Nr. Autorizatie');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('M1', 'Data Autorizatie');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('N1', 'Activitate receptură/laborator');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('O1', 'Mentiuni');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('P1', 'Farmacist șef');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('Q1', 'Observatii');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('R1', 'tel PL');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('S1', 'adresa email PL');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('T1', 'FOND COMERȚ');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('U1', 'MS (DA/NU)');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('V1', 'CFR (DA/NU)');

        $count = 2;

        $rows = $this->getEntries('cfr');

        foreach($rows as $row) {
            $check_ms_vechi = $this->checkMsVchi($row);

            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('A'.$count, $row['persoana_juridica']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('B'.$count, $row['cui']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('C'.$count, $row['localitate_sediu_social']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('D'.$count, $row['adresa_sediu_social']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('E'.$count, $row['Tulcea']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('F'.$count, $row['denumire_pl']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('G'.$count, $row['tip']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('H'.$count, $row['localitate_pl']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('I'.$count, $row['urban_rural']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('J'.$count, $row['adresa_pl']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('K'.$count, $row['Tulcea']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('L'.$count, $row['nr_autorizatie']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('M'.$count, $row['data_autorizare']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('N'.$count, '');
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('O'.$count, '');
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('P'.$count, '');
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('Q'.$count, '');
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('R'.$count, '');
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('S'.$count, '');
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('T'.$count, '');
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('U'.$count, $check_ms_vechi['idEntry'] != '' ? 'DA' : 'NU');
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('V'.$count, 'DA');

            if($check_ms_vechi['idEntry'] == '') {
                $spreadsheet->getActiveSheet()->getStyle('A'.$count.':V'.$count)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('fce3e3');
            }

            $count++;
        }

        $rows = $this->getEntries('ms_vechi');

        foreach($rows as $row) {
            $check_cfr = $this->checkCFR($row);

            if($check_cfr['idEntry'] == '') {
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('A'.$count, $row['denumire'].' '.$row['in_structura']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('B'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('C'.$count, $row['localitate']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('D'.$count, $row['adresa']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('E'.$count, $row['jud_sec']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('F'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('G'.$count, $row['tip']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('H'.$count, $row['localitate']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('I'.$count, $row['urban_rural']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('J'.$count, $row['adresa']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('K'.$count, $row['jud_sec']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('L'.$count, $row['aut']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('M'.$count, $row['data_infiintare']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('N'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('O'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('P'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('Q'.$count, $row['observatii']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('R'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('S'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('T'.$count, $row['fond_comert']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('U'.$count, 'DA');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('V'.$count, 'NU');

                $spreadsheet->getActiveSheet()->getStyle('A'.$count.':V'.$count)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('fffcce');

                $count++;
            }
        }

        $rows = $this->getEntries('ms_vechi');

        foreach($rows as $row) {
            $check_closed = $this->checkClosed($row);

            if($check_closed['idEntry'] != '') {
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('A'.$count, $row['denumire'].' '.$row['in_structura']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('B'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('C'.$count, $row['localitate']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('D'.$count, $row['adresa']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('E'.$count, $row['jud_sec']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('F'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('G'.$count, $row['tip']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('H'.$count, $row['localitate']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('I'.$count, $row['urban_rural']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('J'.$count, $row['adresa']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('K'.$count, $row['jud_sec']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('L'.$count, $row['aut']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('M'.$count, $row['data_infiintare']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('N'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('O'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('P'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('Q'.$count, $row['observatii']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('R'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('S'.$count, '');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('T'.$count, $row['fond_comert']);
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('U'.$count, 'DA');
                $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('V'.$count, 'NU');

                $spreadsheet->getActiveSheet()->getStyle('A'.$count.':V'.$count)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('d3ebff');

                $count++;
            }
        }

        $spreadsheet->getActiveSheet()->setTitle('MS_NOU');
        $spreadsheet->createSheet();

        $spreadsheet->getActiveSheet()->getStyle('A1:V1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('dce6f1');

        $spreadsheet->getActiveSheet()->getStyle('A1:V1')->getFont()->setBold(true);

        for($col = 'A'; $col !== 'W'; $col++) {
            $spreadsheet->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());

        $spreadsheet->getActiveSheet()->freezePane('A2');

        /**
         * MS vechi
         */
        $sheet_count = 1;

        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('A1', 'DENUMIRE');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('B1', 'IN STRUCTURA');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('C1', 'FORM.JUR.');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('D1', 'JUD/SECT');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('E1', 'LOCALITATE');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('F1', 'URBAN/RURAL');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('G1', 'ADRESA');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('H1', 'AUT');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('I1', 'NOTA');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('J1', 'DATA INFIINTARII');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('K1', 'AUT.PRESCHIMBATA');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('L1', 'NOTA');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('M1', 'DATA');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('N1', 'SUSPENDATA PINA LA');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('O1', 'OFICINE LOCALE DE DISTRIBUTIE');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('P1', 'EXCEPTIE');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('Q1', 'OBSERVATII');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('R1', 'FOND COMERT');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('S1', 'UPDATE');

        $count = 2;

        $rows = $this->getEntries('ms_vechi');

        foreach($rows as $row) {
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('A'.$count, $row['denumire']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('B'.$count, $row['in_structura']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('C'.$count, $row['form_jur']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('D'.$count, $row['jud_sec']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('E'.$count, $row['localitate']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('F'.$count, $row['urban_rural']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('G'.$count, $row['adresa']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('H'.$count, $row['aut']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('I'.$count, $row['nota']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('J'.$count, $row['data_infiintare']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('K'.$count, $row['aut_preschimbata']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('L'.$count, $row['nota_1']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('M'.$count, $row['data']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('N'.$count, $row['suspendata_pana_la']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('O'.$count, $row['oficine_locale']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('P'.$count, $row['exceptie']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('Q'.$count, $row['observatii']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('R'.$count, $row['fond_comert']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('S'.$count, $row['update']);

            $count++;
        }

        $spreadsheet->getActiveSheet()->setTitle('MS_VECHI');
        $spreadsheet->createSheet();

        $spreadsheet->getActiveSheet()->getStyle('A1:S1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('dce6f1');

        $spreadsheet->getActiveSheet()->getStyle('A1:S1')->getFont()->setBold(true);

        $to_text = ['H', 'I', 'J', 'K', 'L', 'M', 'N', 'S'];

        foreach($to_text as $to_text_col) {
            $spreadsheet->getActiveSheet()->getStyle($to_text_col.'2:'.$to_text_col.$count)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        for($col = 'A'; $col !== 'T'; $col++) {
            $spreadsheet->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());

        $spreadsheet->getActiveSheet()->freezePane('A2');

        /**
         * CFR
         */
        $sheet_count = 2;

        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('A1', 'Persoana Juridica');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('B1', 'CUI');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('C1', 'Localitate Sediu Social');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('D1', 'Adresa Sediu Social');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('E1', 'Tip UNITATE');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('F1', 'Localitate Farmacie');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('G1', 'Tip Loc Farmacie');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('H1', 'Adresa Farmacie');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('I1', 'Denumire Farmacie');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('J1', 'Nr. Autorizatie');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('K1', 'Nota');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('L1', 'Data Autorizatie');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('M1', 'Suspendare');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('N1', 'Stare');
        $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('O1', 'Obs');

        $count = 2;

        $rows = $this->getEntries('cfr');

        foreach($rows as $row) {
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('A'.$count, $row['persoana_juridica']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('B'.$count, $row['cui']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('C'.$count, $row['localitate_sediu_social']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('D'.$count, $row['adresa_sediu_social']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('E'.$count, $row['tip']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('F'.$count, $row['localitate_pl']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('G'.$count, $row['urban_rural']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('H'.$count, $row['adresa_pl']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('I'.$count, $row['denumire_pl']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('J'.$count, $row['nr_autorizatie']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('K'.$count, $row['nota']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('L'.$count, $row['data_autorizare']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('M'.$count, $row['suspendare']);
            $spreadsheet->setActiveSheetIndex($sheet_count)->setCellValue('N'.$count, $row['stare']);

            $count++;
        }

        $spreadsheet->getActiveSheet()->setTitle('CFR');
        $spreadsheet->createSheet();

        $spreadsheet->getActiveSheet()->getStyle('A1:O1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('dce6f1');

        $spreadsheet->getActiveSheet()->getStyle('A1:O1')->getFont()->setBold(true);

        $to_text = ['B', 'J', 'K', 'L'];

        foreach($to_text as $to_text_col) {
            $spreadsheet->getActiveSheet()->getStyle($to_text_col.'2:'.$to_text_col.$count)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        for($col = 'A'; $col !== 'P'; $col++) {
            $spreadsheet->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());

        $spreadsheet->getActiveSheet()->freezePane('A2');

        $spreadsheet->setActiveSheetIndex(0);

        $spreadsheet->removeSheetByIndex(
            $spreadsheet->getIndex(
                $spreadsheet->getSheetByName('Worksheet')
            )
        );

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="raport_ms.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 2019 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        ob_end_clean();
        ob_start();

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        die();
    }
}