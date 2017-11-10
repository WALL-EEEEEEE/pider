<?php
namespace Util\Data;

/**
 * @class Required 
 *
 * Define data required by analystic
 */
use Extension\DBExtension;
class Required {

    public static function require($name,$sql,$cached = true) {
        $cached_file= APP_ROOT.'/cache/.data/'.$name.'.db';
        $data = [];
        if(file_exists($cached_file)) {
            $data = file_get_contents($cached_file); 
            return json_decode($data,true);
        }
        $data = DBExtension::get_all($sql); 
        if ($cached) {
            file_put_contents($cached_file,json_encode($data));
        } 
        return $data;
    }
}

