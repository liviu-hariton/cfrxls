<?php

/**
* Implementation of CsvInterface
*/
class Csv implements CsvInterface
{

    /**
    * CSV filename with path
    * @type string
    */
    protected $filename;

    /**
    * CSV separator
    * @type string
    */
    protected $separator;

    /**
    * CSV file resource link
    * @type resource
    */
    protected $csvH;


    public function __construct(/*string*/ $filename, /*string*/ $separator = ";")
    {
        if (!is_string($filename)) {
            throw new Exception("Illegal parameter filename. Must be string.");
        }
        if (!is_string($separator)) {
            throw new Exception("Illegal parameter separator. Must be string.");
        }
        $this->filename = $filename;
        $this->separator = $separator;
    }

    public function __destruct()
    {
        if (is_resource($this->csvH)) {
            fclose($this->csvH);
        }
    }

    public function read(/*integer*/ $limit = 3000)
    {
        if (!is_integer($limit)) {
            throw new Exception("Illegal parameter limit. Must be integer.");
        }
        try {
            while(empty($row)) {
                $row = fgetcsv($this->getCsvH(), $limit, $this->separator);
                if (!$row) {
                    return false;
                }
            }
        }
        catch (Exception $e) {
            throw $e;
        }
        return $row;
    }

    public function readAll()
    {
        try {
            $this->rewind();
            while($row = $this->read()) {
                $csv[] = $row;
            }
        }
        catch (Exception $e) {
            throw $e;
        }
        return $csv;
    }

    public function write(/*array*/ $add, /*boolean*/ $toEnd = true)
    {
        if (!is_bool($toEnd)) {
            throw new Exception("Illegal parameter toEnd. Must be boolean.");
        }
        if (!is_array($add)) {
            throw new Exception("Illegal parameter add. Must be array.");
        }
        try {
            if ($toEnd) {
                $this->toEnd();
            }
            fwrite($this->getCsvH(), implode($this->separator, $add));
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    public function seek(/*integer*/ $position = 0, /*integer*/ $offset = 0)
    {
        if (!is_integer($position)) {
            throw new Exception("Illegal parameter position. Must be integer.");
        }
        if (!is_integer($offset)) {
            throw new Exception("Illegal parameter offset. Must be integer.");
        }
        try {
            if ($position < 0) {
                if (fseek($this->getCsvH(), $offset, SEEK_SET) < 0) {
                    throw new Exception("Cannot seek cursor in CSV file on '". $offset ."'.");
                }
            }
            elseif ($position > 0) {
                if (fseek($this->getCsvH(), $offset, SEEK_END) < 0) {
                    throw new Exception("Cannot seek cursor in CSV file on END + '". $offset ."'.");
                }
            }
            else {
                if (fseek($this->getCsvH(), $offset, SEEK_CUR) < 0) {
                    throw new Exception("Cannot seek cursor in CSV file on CURRENT + '". $offset ."'.");
                }
            }
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    public function rewind()
    {
        if (!@rewind($this->getCsvH()) === 0) {
            throw new Exception("Cannot rewind cursor in CSV file.");
        }
    }

    /**
    * seek CSV file to end
    * @return void
    */
    protected function toEnd()
    {
        if (!fseek($this->getCsvH(), 0, SEEK_END)){
            throw new Exception("Cannot seek cursor in CSV file to end.");
        }
    }

    /**
    * open file defined with filename
    * @return void
    */
    protected function open()
    {
        if (is_resource($this->csvH)) {
            return true;
        }
        if (!strlen($this->filename)) {
            throw new Exception("There is no filename parameter.");
        }		
		if (!$this->csvH = @fopen($this->filename, "r")) {
            throw new Exception("Cannot find/open '". $this->filename ."'.");
        }
        return true;
    }

    /**
    * Getter of csvH
    * @return resource
    */
    protected function getCsvh()
    {
        try {
            $this->open();
        }
        catch (Exception $e) {
            throw $e;
        }
        return $this->csvH;
    }
}

?>