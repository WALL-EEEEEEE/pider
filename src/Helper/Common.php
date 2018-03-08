<?php

/**
 * This function is used to spawn a 16-bit random guid string.
 * @return string
 */
function spawn_guid() {
    if (function_exists('com_create_guid') == true) {
        return trim(com_create_guid(),"{}");
    }
    return strtolower(sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
};

//create directory recursively
function createDir($dir)
{
    $dirs = explode('/',$dir);
    $tmp_dir = "/";
    foreach($dirs as $dir_val)
    {
        $tmp_dir.= $dir_val . "/";
        if (!file_exists($tmp_dir))
        {
            mkdir($tmp_dir);
        }
    }
}

//read html file
function readHtml($uid,$prepath='')
{
        $path = "/data/html/";
        if(!empty($prepath)) {
            $path = $prepath;
        }
        $newpath = $path . $uid . '.txt';
        if (!file_exists($newpath))
                return false;
        $html = file_get_contents($newpath);
        return $html;
}

//write html file
function writeHtml($uid,$html,$prepath='')
{
        $path = '/data/html/';
        if ($uid == '')
                return false;

        if(!empty($prepath)) {
            $path = $prepath;
        } 
        createDir($path);
        $newpath = $path . $uid . '.txt';
        //覆盖
        $myfile = fopen($newpath, "w");
        fwrite($myfile, $html);
        fclose($myfile);

        return true;
}

//detect a string if is html string
function is_html($string) {
    return !($string == strip_tags($string,'<br>'));
}

//validate a xpath string
function validate_xpath($string) {
    $dom = new DOMDocument();
    $xpather = new DOMXPath($dom);
    if (!@$xpather->query($string)) {
       return false;
    }
    return true;
}
/*
 * inspect if is an numberic array
 * @param @array array()
 * @return if it is a numberic array return true or false
 * */
function is_numeric_array($array){
    if(is_array($array)) {
        foreach($array as $key => $value) {
            if($value !== (int)$value ) {
                return false;
            }
        }
        return true;
    }
    return false;
}

/**
 *inspect if is an numberic array and all elements are positive 
 */
function is_pos_array($array){
    if(is_array($array)) {
        foreach($array as $key => $value) {
            if($value !== int($value) || $value < 0) {
                return false;
            }
        }
        return true;
    }
    return false;
}

/**
 * Screw an array up to an odd array,like array(1,3,5,...)
 */
function odd_array($array) {
    if (!is_numeric_array($array)) {
        return false;
    }
    $array = array_filter($array,function($value){
        return ($value & 1);
    });
    return $array;
}


/**
 *Screw an array up to an even array, like array(2,4,6,8,...)
 */
function even_array($array){
    if (!is_numeric_array($array)) {
        return false;
    }
    $array = array_filter($array,function($value){
        return !($value & 1);
    });
    return $array;

}

/**
 * construct a numberic array rapidly
 */
function narrays($start,$end) {
    $array = array();
    if(is_numeric($start) && is_numeric($end) && $start > 0 && $end > 0 && $start < $end ) {
        while($start < $end) {
            $array[] = $start;
            $start++;
        }
        return $array;
    }
    return false;
}

/**
 *
 * quote all array values
 *
 */
function array_quote($array,$option_wrapper="'") {
    $dest_array = $array;
    if (empty($dest_array)) {
        throw new \ErrorException('Argument Error: an array must be provided'.gettype($dest_array).' finded');
    }
    array_walk_recursive($dest_array,function(&$value,$key) use ($option_wrapper){
        $value = $option_wrapper.$value.$option_wrapper;
    });
    return $dest_array;
}

function gbk_to_utf8($str)
{
    return mb_convert_encoding($str, 'utf-8', 'gbk');
}

