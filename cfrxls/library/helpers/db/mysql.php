<?php

class Db {
    public $db_connect_id;
    public $query_result;
    public $row = array();
    public $rowset = array();
    public $num_queries = 0;
    public $queries = array();
    public $last_query = array();

    const _BEGIN_TRANSACTION = 1;
    const _END_TRANSACTION = 2;

    public function __construct($sqlserver, $sqluser, $sqlpassword, $database)	{
        global $dev_ips;

        $this->dev_ips = $dev_ips;

        $this->user = $sqluser;
        $this->password = $sqlpassword;
        $this->server = $sqlserver;
        $this->dbname = $database;

        $this->db_connect_id = @mysqli_connect($this->server, $this->user, $this->password, $this->dbname);

        if($this->db_connect_id) {
            return $this->db_connect_id;
        } else {
            return false;
        }
    }

    public function sqlClose() {
        if($this->db_connect_id) {
            if($this->query_result) {
                @mysqli_free_result($this->query_result);
            }
            $result = @mysqli_close($this->db_connect_id);
            return $result;
        } else {
            return false;
        }
    }

    public function printr($input) {
        // 13.10.2021 (Cristian Parvu) - corectat notice SSH_CLIENT lipsa
        $ssh_client = array();

        if (isset($_SERVER['SSH_CLIENT'])) {
            $ssh_client = explode(" ", $_SERVER['SSH_CLIENT']);
        }

        if(@in_array(getenv('REMOTE_ADDR'), $this->dev_ips) || (isset($ssh_client[0]) && in_array($ssh_client[0], $this->dev_ips))) {
            echo '<pre>';
            print_r($input);
            echo '</pre>'."\n";
        }
    }

    public function sqlQuery($query = "") {
        unset($this->query_result);

        /*echo "<pre>";echo $query;echo "</pre>";*/

        if($query != "") {
            $this->num_queries++;

            $started = microtime(true);

            $this->query_result = @mysqli_query($this->db_connect_id, $query);

            $end = microtime(true);

            $difference = $end - $started;

            $queryTime = number_format($difference, 10);

            if(_SQL_TIME === true) {
                $this->printr('['.$queryTime.' s]: '.$query);
            }
        }

        // 31.10.2021 (Cristian Parvu) - verifica rezultatul interogarii
        if(isset($this->query_result)) {
            return $this->query_result;
        } else {
            // 14.10.2021 (Cristian Parvu) - $transaction - variabila nu pare sa fie folosita nicaieri
            // return ( $transaction == self::_END_TRANSACTION ) ? true : false;
            return false;
        }
    }

    public function sqlInsert($table, $values, $filter = array()) {
        $query = "insert into ".$table;
        $field = "";
        $field_value = "";
        foreach($values as $k=>$v) {
            if(!in_array($k, $filter)) {
                $field .= "`".$this->sqlCleanInput($k)."`,";
                $field_value .= $v != 'NULL' ? "'".$this->sqlCleanInput($v)."'," : "NULL,";
            }
        }
        $query .= " (".substr($field, 0, -1).") values (".substr($field_value, 0, -1).")";

        return $this->sqlQuery($query);
    }

    public function sqlUpdate($table, $values, $filter = array(), $where = "") {
        $query = "update ".$table." set ";
        $string_ = "";
        if($where != "") {
            foreach($values as $k=>$v) {
                if(!in_array($k, $filter)/* && !empty($v)*/) {
                    $string_ .=  $v != 'NULL' ? "`".$this->sqlCleanInput($k)."` = '".$this->sqlCleanInput($v)."', " : "`".$this->sqlCleanInput($k)."` = NULL, ";
                }
            }
            $query .= substr($string_, 0, -2)." where ".$where;
        } else {
            foreach($values as $k=>$v) {
                if(!in_array($k, $filter) && !empty($v)) {
                    $string_ .=  $v != 'NULL' ? "`".$this->sqlCleanInput($k)."` = '".$this->sqlCleanInput($v)."', " : "`".$this->sqlCleanInput($k)."` = NULL, ";
                }
            }
            $query .= substr($string_, 0, -2);
        }

        return $this->sqlQuery($query);
    }

    public function sqlSelect($table, $orderby = '', $direction = '', $limit = '', $where = array(), $what = "*") {
        $query = "select ".$this->sqlCleanInput($what)." from `".$this->sqlCleanInput($table)."`";
        if(is_array($where) && count($where) > 0) {
            $query .= " where ";
            $i = 0;
            foreach($where as $criteria) {
                $temp = explode(' ', $criteria);
                $temp[0] = "`".$this->sqlCleanInput($temp[0])."`";
                $temp[1] = $temp[1];
                $temp[2] = "'".$this->sqlCleanInput(str_replace('-', ' ', $temp[2]))."'";
                $criteria = $temp[0].$temp[1].$temp[2];
                if($i == 0) {
                    $query .= $criteria ;
                }
                if($i > 0) {
                    $query .= " and ".$criteria;
                }
                $i++;
            }
        }
        if($orderby != '') {
            $query .= " order by ".$this->sqlCleanInput($orderby);
        }
        if($direction != '') {
            $query .= " ".$this->sqlCleanInput($direction);
        }
        if($limit != '') {
            $query .= " limit ".$this->sqlCleanInput($limit);
        }

        return $this->sqlQuery($query);
    }

    public function sqlCrossJoin($columns = array(), $tables = array()) {
        $query = 'select ';
        $cols = '';
        $tbls = '';

        foreach($columns as $column) {
            $cols .= $this->sqlCleanInput($column).',';
        }

        $query .= substr($cols, 0, -1).' from ';

        foreach($tables as $table) {
            $tbls .= $this->sqlCleanInput($table).',';
        }

        $query .= substr($tbls, 0, -1).' ';

        return $this->sqlQuery($query);
    }

    public function sqlJoin($direction = 'inner', $columns = array(), $tables = array(), $pairs = array(),  $using = '', $where = '') {
        if(is_array($tables) && count($tables) == 2 && is_array($pairs)  && count($pairs) == 2) {
            $query = 'select ';

            $cols = '';
            $tbls = '';

            switch($direction) {
                case "inner":
                    foreach($columns as $column) {
                        $cols .= '`'.$this->sqlCleanInput($column).'`,';
                    }

                    $query .= substr($cols, 0, -1).' from ';

                    foreach($tables as $table) {
                        $tbls .= $this->sqlCleanInput($table).',';
                    }

                    $query .= substr($tbls, 0, -1).' where ('.$this->sqlCleanInput($tables[0]).'.`'.$this->sqlCleanInput($pair[0]).'` = '.$this->sqlCleanInput($tables[1]).'.`'.$this->sqlCleanInput($pair[1]).'`)';
                    break;
                case "left":
                    foreach($columns as $column) {
                        $cols .= '`'.$this->sqlCleanInput($column).'`,';
                    }

                    $query .= substr($cols, 0, -1).' from '.$this->sqlCleanInput($tables[0]).' left join '.$this->sqlCleanInput($tables[1]).' on ';
                    $query .= $this->sqlCleanInput($tables[0]).'.`'.$this->sqlCleanInput($pair[0]).'` = '.$this->sqlCleanInput($tables[1]).'.`'.$this->sqlCleanInput($pair[1]).'`';
                    if($using != '') {
                        $query .= ' using '.$this->sqlCleanInput($using);
                    }
                    if($where != '') {
                        $query .= ' where '.$where;
                    }
                    break;
                case "right":
                    foreach($columns as $column) {
                        $cols .= '`'.$this->sqlCleanInput($column).'`,';
                    }

                    $query .= substr($cols, 0, -1).' from '.$this->sqlCleanInput($tables[0]).' right join '.$this->sqlCleanInput($tables[1]).' on ';
                    $query .= $this->sqlCleanInput($tables[0]).'.`'.$this->sqlCleanInput($pair[0]).'` = '.$this->sqlCleanInput($tables[1]).'.`'.$this->sqlCleanInput($pair[1]).'`';
                    if($where != '') {
                        $query .= ' where '.$where;
                    }
                    break;
                default:

            }

            return $this->sqlQuery($query);
        } else {
            return false;
        }
    }

    public function sqlSelectInsert() {
        //TODO
    }

    public function sqlUpdateJoin($tables = array(), $pairs = array(), $where = '') {
        $query = 'update ';
        $tbls = '';
        $flds = '';

        foreach($tables as $table) {
            $tbls .= $this->sqlCleanInput($table).',';
        }

        $query .= substr($tbls, 0, -1).' set ';

        foreach($pairs as $field=>$value) {
            $flds .= '`'.$this->sqlCleanInput($field).'` = '.$this->sqlCleanInput($value).',';
        }

        $query .= substr($flds, 0, -1).' where '.$where;

        return $this->sqlQuery($query);
    }

    public function sqlDeleteJoin($fields = array(), $tables = array(), $where = '') {
        $query = 'delete ';
        $flds = '';
        $tbls = '';

        foreach($fields as $field) {
            $flds .= '`'.$this->sqlCleanInput($field).'`,';
        }

        $query .= substr($flds, 0, -1).' from ';

        foreach($tables as $table) {
            $tbls .= $this->sqlCleanInput($table).',';
        }

        $query .= substr($tbls, 0, -1);

        if($where != '') {
            $query .= ' where '.$where;
        }

        return $this->sqlQuery($query);
    }

    public function sqlNumrows($query_id = 0)	{
        if(!$query_id) {
            $query_id = $this->query_result;
        }
        if($query_id) {
            $result = @mysqli_num_rows($query_id);
            return $result;
        } else {
            return false;
        }
    }

    public function sqlAffectedRows()	{
        if($this->db_connect_id) {
            $result = @mysqli_affected_rows($this->db_connect_id);
            return $result;
        } else {
            return false;
        }
    }

    public function sqlNumfields($query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }
        if($query_id) {
            $result = @mysqli_num_fields($query_id);
            return $result;
        } else {
            return false;
        }
    }

    public function mysqli_field_name($result, $field_offset) {
        $properties = mysqli_fetch_field_direct($result, $field_offset);
        return is_object($properties) ? $properties->name : null;
    }

    public function sqlFieldName($offset, $query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }
        if($query_id) {
            $result = $this->mysqli_field_name($query_id, $offset);
            return $result;
        } else {
            return false;
        }
    }

    public function sqlFetchRow($query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }
        if($query_id) {
            @$this->row = @mysqli_fetch_array($query_id);

            return @$this->row;
        } else {
            return false;
        }
    }

    public function sqlFetchAssoc($query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }
        if($query_id) {
            $this->row = @mysqli_fetch_assoc($query_id);

            return $this->row;
        } else {
            return false;
        }
    }

    public function sqlFetchField($field, $rownum = -1, $query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }
        if($query_id) {
            if($rownum > -1) {
                $result = @mysqli_result($query_id, $rownum, $field);
            } else {
                if(empty($this->row[$query_id]) && empty($this->rowset[$query_id]))	{
                    if($this->sqlFetchRow()) {
                        $result = $this->row[$query_id][$field];
                    }
                } else {
                    if($this->rowset[$query_id]) {
                        $result = $this->rowset[$query_id][0][$field];
                    } else if($this->row[$query_id]) {
                        $result = $this->row[$query_id][$field];
                    }
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    public function sqlRowSeek($rownum, $query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }
        if($query_id) {
            $result = @mysqli_data_seek($query_id, $rownum);
            return $result;
        } else {
            return false;
        }
    }

    public function sqlLastId() {
        if($this->db_connect_id) {
            $result = @mysqli_insert_id($this->db_connect_id);
            return $result;
        } else {
            return false;
        }
    }

    public function sqlFreeResult($query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }

        if ( $query_id ) {
            unset($this->row[$query_id]);
            unset($this->rowset[$query_id]);

            @mysqli_free_result($query_id);

            return true;
        } else {
            return false;
        }
    }

    public function sqlError($query_id = 0) {
        if(!$this->db_connect_id) {
            $result["message"] = @mysqli_error();
            $result["code"] = @mysqli_errno();
        } else {
            $result["message"] = @mysqli_error($this->db_connect_id);
            $result["code"] = @mysqli_errno($this->db_connect_id);
        }

        return $result;
    }

    public function sqlCleanInput($input) {
        /*
        if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) { // 27.11.2021 (Cristian Parvu) - funcția get_magic_quotes_gpc() eliminată începând cu PHP 7.4.0+
            $input = stripslashes($input);
        }
        */
        if(!is_numeric($input)) {
            $input = mysqli_real_escape_string($this->db_connect_id, $input);
        }
        return $input;
    }

    public function sqlInfo($what) {
        $info = array();

        switch($what) {
            case "all":
                $info['server'] = mysqli_get_server_info();
                $info['client'] = mysqli_get_client_info();
                $info['host'] = mysqli_get_host_info();
                $info['protocol'] = mysqli_get_proto_info();
                break;
            case "server":
                $info['server'] = mysqli_get_server_info();
                break;
            case "client":
                $info['client'] = mysqli_get_client_info();
                break;
            case "host":
                $info['host'] = mysqli_get_host_info();
                break;
            case "protocol":
                $info['protocol'] = mysqli_get_proto_info();
                break;
            default:
        }

        return $info;
    }

    public function sqlQueryInfo() {
        if(!$this->db_connect_id) {
            $result = @mysqli_info();
        } else {
            $result = @mysqli_info($this->db_connect_id);
        }

        return $result;
    }

    private function sqlStoreLastQuery($input) {
        $sqls = array();

        $sqls['query'] = $input;
        $sqls['errors'] = $this->sqlError();

        $this->queries[] = $sqls;
    }

    private function sqlStoreQueries($input) {
        $sqls = array();

        $sqls['query'] = $input;
        $sqls['errors'] = $this->sqlError();

        $this->last_query = $sqls;
    }

    public function sqlListQueries() {
        return $this->queries;
    }
}
