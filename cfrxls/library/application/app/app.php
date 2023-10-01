<?php

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

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
}