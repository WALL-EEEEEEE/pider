<?php
namespace Pider\Support;

use Pider\Spider;

class SpiderWise {

    public static function isSpider(string $class,$directory='') {
        include_once($class);
        $cls = pathinfo($class,PATHINFO_FILENAME); 
        $pcls = @class_parents($cls,false);
        if (class_exists($cls,false) && in_array('Pider\Spider',$pcls)) {
            return true;
        } 
        return false;
    }

    public static function listSpider(string $directory) {
        $spiders = [];
        $files = scandir($directory); 
        foreach($files as $file) {
            $file = $directory.DIRECTORY_SEPARATOR.$file;
            if(!is_dir($file) && pathinfo($file,PATHINFO_EXTENSION)) {
                if(self::isSpider($file)) {
                    $cls = pathinfo($file,PATHINFO_FILENAME); 
                    $spiders[] = $cls;
                }
            } 
        }
        return $spiders;
    }

    public static function linkSpider(string $url) {
    }
} 

