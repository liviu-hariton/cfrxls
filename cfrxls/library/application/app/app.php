<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class App
{
    public function __construct()
    {
        global $db, $etc, $view;

        $this->db = $db;
        $this->etc = $etc;
        $this->view = $view;
    }

    protected array $files = [
        'ms_vechi', 'ms_nou', 'cfr', 'inchise'
    ];

    public function upload(): void
    {
        $this->createSessionFolder();

        foreach($this->files as $file) {
            if(!empty($_FILES[$file]['name']) && $_FILES[$file]['error'] == '0') {
                move_uploaded_file($_FILES[$file]['tmp_name'], _UPLOAD_ROOT._SID.'/'.$file.'.xls');
            }
        }

        $this->etc->redirect(_SITE_URL.'?uploadsuccess');
    }

    public function reset()
    {

    }

    private function createSessionFolder(): void
    {
        if(!file_exists(_UPLOAD_ROOT._SID)) {
            mkdir(_UPLOAD_ROOT._SID);
        }
    }
}