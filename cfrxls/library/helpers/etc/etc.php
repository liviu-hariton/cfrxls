<?php

class Etc {
    public function splitString($string, $num) {
       $done = 0;
       $leters = 0;
       $fraze = '';
       $words = explode(" ", trim($string));
       $total_words = count($words);
       for($i = 0; $i < $total_words; $i++) {
           $word_array = preg_split('//', $words[$i], -1, PREG_SPLIT_NO_EMPTY);
           $leters = $leters + count($word_array);
           
           if (($leters > $num) && ($done == 0)) {
           $fraze = trim($fraze) . "...";
               $done = 1;
           } 
           if ($done == 0) {
               $fraze .= $words[$i] . " ";
           } 
       } 
       return $fraze;
    }
    
    public function cleanOutput($output) {
        $output = htmlspecialchars($output, ENT_QUOTES);
        
        $words = explode(" ", $output);
        $allow = TRUE;
        foreach($words as $word) {
            if(strlen($word) > 200) {
                $allow = FALSE;
            }
        }
        
        $src = array('"');
        $rpl = array('&quot;');
        
        $output = str_replace($src, $rpl, $output);
        
        if($allow) {
            return stripslashes($output);
        } else {
            return "";
        }
    }
    
    public function randomString($lenght = '8') {
        $string = NULL;
        for($i = 0; $i < $lenght; $i++) {
            $char = chr(rand(48,122));
            while (!preg_match("/[a-zA-Z0-9]/", $char)){
                if($char == $lenght) continue;
                $char = chr(rand(48,90));
            }
            $string .= $char;
            $lchar = $char;
        }
        return $string;
    }
    
    public function randomNumber($lenght = '8') {
        $string = NULL;
        for($i = 0; $i < $lenght; $i++) {
            $char = chr(rand(48,122));
            while (!preg_match("/[1-9]/", $char)){
                if($char == $lenght) continue;
                $char = chr(rand(48,90));
            }
            $string .= $char;
            $lchar = $char;
        }
        return $string;
    }
    
    public function printr($input) {
        echo '<pre class="pre-dump">';
        print_r($input);
        echo '</pre>';
    }
    
    public function redirect($url = _SITE_URL, $parameters = '', $permanent = false) {

        if($permanent === true) {
            header("HTTP/1.1 301 Moved Permanently");
        }

        header("location: ".$url.$parameters);
        ob_end_clean();
        exit;
    }
    
    public function safeLink($str, $replace = array(), $delimiter = '-', $maxLength = 200) {
        if(!empty($replace)) {
            $str = str_replace((array)$replace, ' ', $str);
        }
        
        $src = array('&amp;');
        $rpl = array('');
        
        $str = str_replace($src, $rpl, $str);
    
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim(substr($clean, 0, $maxLength), '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        
        return $clean;
    }
    
    public function safeFileLink($str, $replace = array(), $delimiter = '-', $maxLength = 200) {
        if(!empty($replace)) {
            $str = str_replace((array)$replace, ' ', $str);
        }
        
        $src = array('&amp;');
        $rpl = array('');
        
        $str = str_replace($src, $rpl, $str);
    
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_\.|+ -]/", '', $clean);
        $clean = strtolower(trim(substr($clean, 0, $maxLength), '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        
        return $clean;
    }
    
    public function setAlias($input, $replace = array(), $delimiter = '-', $maxLength = 200) {
        return $this->safeLink($input, $replace, $delimiter, $maxLength);
    }
    
    public function truncate($text, $numb, $etc = "...") {
        if(strlen($text) > $numb) {
            $text = substr($text, 0, $numb);
            
            $punctuation = ""; //punctuation characters to remove
            
            $text = (strspn(strrev($text), $punctuation) != 0) ? substr($text, 0, -strspn(strrev($text), $punctuation)) : $text;
            
            $text = $text.$etc;
        }
        
        return $text;
    }
    
    public function shrinkText($text, $numb, $etc = "...") {
        $first_part = $this->truncate($this->truncate($text, strlen($text) / 2, ""), $numb / 2, "");
        $second_part = $this->truncate(strrev($this->truncate(strrev($text), strlen($text) / 2, "")), $numb / 2, "");
        
        return $first_part.$etc.$second_part;
    }
    
    public function shortenUrl($input) {
        $apiKey = 'MyAPIKey';

        $postData = array('longUrl' => $input);
        $jsonData = json_encode($postData);
        
        $curlObj = curl_init();
        
        curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?fields=id&key='._G_SHORT_URL);
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);
        
        $response = curl_exec($curlObj);
        
        $json = json_decode($response);
        
        curl_close($curlObj);
        
        return $json->id;
    }
    
    public function checkRobot() {  
        $spiders = array(  
            'Googlebot', 'Yammybot', 'Openbot', 'Yahoo', 'Slurp', 'msnbot',  
            'ia_archiver', 'Lycos', 'Scooter', 'AltaVista', 'Teoma', 'Gigabot',  
            'Googlebot-Mobile'  
        );  
        
        foreach($spiders as $spider) {  
            if(eregi($spider, $_SERVER['HTTP_USER_AGENT'])) {  
                return TRUE;  
            }  
        }  
        
        return FALSE;  
    }
    
    public function flattArray($array) {
        if(!is_array($array)) {
            return array($array);
        }
        
        $result = array();
        
        foreach($array as $value) {
            $result = array_merge($result, $this->flattArray($value));
        }
        
        return $result;
    }
    
    public function startLoadingTime() {
        $imicrotime = microtime();
        
        $imicrotime = explode(' ', $imicrotime);
        
        return $imicrotime[1] + $imicrotime[0]; 
    }
    
    public function endLoadingTime() {
        $imicrotime = microtime();
        
        $imicrotime = explode(' ', $imicrotime);
        
        return $imicrotime[1] + $imicrotime[0];
    }
    
    public function getLoadingTime($start, $end) {
        return number_format(round(($end - $start), 5), 2, '.', ' ');
    }
    
    public function getIp() {
        $ipaddress = '0.0.0.0';
        
        if(getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if(getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if(getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if(getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if(getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if(getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        }
     
        return $ipaddress;
    }
    
    public function base64UrlDecode($_input) {
        return base64_decode(str_replace(array('_','-',','),array('=','+','/'),$_input));
    }
    
    public function base64UrlEncode($_input) {
       return str_replace(array('=','+','/'),array('_','-',','),base64_encode($_input));
    }
    
    public function unsetByValue($array, $val = '', $preserve_keys = true) {
        if(empty($array) || !is_array($array)) {
            return false;
        }
        
        if(!in_array($val, $array)) {
            return $array;
        }
    
        foreach($array as $key=>$value) {
            if($value == $val) unset($array[$key]);
        }
    
        return ($preserve_keys === true) ? $array : array_values($array);
    }
    
    public function birthdayFromCNP($cnp) {
      if (strlen($cnp) == 13) {
        $sex = $cnp[0];
          
        $bd = substr($cnp, 1, 6);
        
        if($sex == 1 || $sex == 2) {
             $sy = 19; 
        } elseif ($sex == 3 || $sex == 4) {
            $sy = 18; 
        } elseif ($sex == 5 || $sex == 6) {
             $sy = 20; 
        }
        
        $year = $sy.$bd[0].$bd[1];
        $month = $bd[2].$bd[3];
        $day = $bd[4].$bd[5];
        
        $birthday = strtotime("$year-$month-$day");
        
        return $birthday;
      } else {
        return false;
      }
    }
    
    public function arraySearchKeyByValue($haystack, $needle, $search_name, $return_name) {
        foreach($haystack as $haystack_key=>$haystack_item) {
            if($haystack_item[$search_name] === $needle) {
                return $haystack[$haystack_key][$return_name];
            }
        }
        
        return false;
    }
    
    public function netMatch($network, $ip) {
        $network = trim($network);
        $orig_network = $network;
        $ip = trim($ip);
        
        if($ip == $network) {
            return TRUE;
        }
        
        $network = str_replace(' ', '', $network);
        
        if(strpos($network, '*') !== FALSE) {
            if(strpos($network, '/') !== FALSE) {
                $asParts = explode('/', $network);
                $network = @ $asParts[0];
            }
            
            $nCount = substr_count($network, '*');
            $network = str_replace('*', '0', $network);
            
            if($nCount == 1) {
                $network .= '/24';
            } else if ($nCount == 2) {
                $network .= '/16';
            } else if ($nCount == 3) {
                $network .= '/8';
            } else if ($nCount > 3) {
                return TRUE;
            }
        }
    
        $d = strpos($network, '-');
        
        if($d === FALSE) {
            $ip_arr = explode('/', $network);
           
            if (!preg_match("@\d*\.\d*\.\d*\.\d*@", $ip_arr[0], $matches)){
                $ip_arr[0].=".0";
            }
            
            $network_long = ip2long($ip_arr[0]);
            $x = ip2long($ip_arr[1]);
            $mask = long2ip($x) == $ip_arr[1] ? $x : (0xffffffff << (32 - $ip_arr[1]));
            $ip_long = ip2long($ip);
            
            return ($ip_long & $mask) == ($network_long & $mask);
        } else {
            $from = trim(ip2long(substr($network, 0, $d)));
            $to = trim(ip2long(substr($network, $d+1)));
            $ip = ip2long($ip);
            
            return ($ip>=$from and $ip<=$to);
        }
    }
    public function getSettings() {
        $data = array();
        
        $resource = $this->db->sqlQuery(
            "select * from "._MYSQL_PREFIX."settings"
        );
        
        while($item = $this->db->sqlFetchrow($resource)) {
            $data[$item['key']] = $item['value'];
        }
        
        return $data;
    }
    
    public function getFileExtension($file) {
        $filename = basename($file);
    
        $filename = explode('.', $filename);
        
        return $filename[count($filename) - 1];
    }
    
    public function objectToArray($object) {
        if(!is_object($object) && !is_array($object)) {
            return $object;
        } else {
            return array_map('objectToArray', (array) $object);
        }
    }
	
	public function ucName($string) {
    	$string = ucwords(strtolower($string));
		
	    foreach(array('-', '\'') as $delimiter) {
      		if(strpos($string, $delimiter) !== false) {
	        	$string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
	      	}
	    }
		
	    return $string;
	}
    
    public function checkSSL() {
        $check = fsockopen(_MAIN_DOMAIN, 443, $errno, $errstr, 30);
        
        if($check === false) {
            $result = false;
        } else {
            $result = true;
        }
        
        fclose($check);
        
        return $result;
    }

    public function sortCopyArray($input, $direction) {
        if($direction == 'asc') {
            sort($input);
        }

        if($direction == 'desc') {
            rsort($input);
        }

        return $input;
    }

    public function monthName($month_number){
        return ucwords(strftime('%B', mktime(0, 0, 0, $month_number, 10)));
    }

    public function monthEndDate($year, $month_number){
        return date("t", strtotime("$year-$month_number-1"));
    }

    public function zeroPad($number){
        if($number < 10) {
            return "0$number";
        } else {
            return "$number";
        }
    }

    public function getQuarters($start_date, $end_date){
        $quarters = array();

        $start_month = date('m', strtotime($start_date) );
        $start_year = date('Y', strtotime($start_date) );

        $end_month = date('m', strtotime($end_date) );
        $end_year = date('Y', strtotime($end_date) );

        $start_quarter = ceil($start_month / 3);
        $end_quarter = ceil($end_month / 3);

        $quarter = $start_quarter;

        for( $y = $start_year; $y <= $end_year; $y++ ){
            if($y == $end_year) {
                $max_qtr = $end_quarter;
            } else {
                $max_qtr = 4;
            }

            for($q = $quarter; $q <= $max_qtr; $q++){
                $current_quarter = new stdClass();

                $end_month_num = $this->zeroPad($q * 3);
                $start_month_num = ($end_month_num - 2);

                $q_start_month = $this->monthName($start_month_num);
                $q_end_month = $this->monthName($end_month_num);

                $current_quarter->number = $q;
                $current_quarter->number_year = $q.':'.$y;
                $current_quarter->year = $y;
                $current_quarter->period = "Q$q $y ($q_start_month - $q_end_month)";
                $current_quarter->period_start = "$y-$start_month_num-01";
                $current_quarter->period_end = "$y-$end_month_num-".$this->monthEndDate($y, $end_month_num);

                $quarters[] = $current_quarter;

                unset($current_quarter);
            }

            $quarter = 1;
        }

        return $quarters;
    }

    public function getCurrentQuarter($month) {
        $quarter = floor(($month - 1) / 3) + 1;

        return $quarter;
    }
}