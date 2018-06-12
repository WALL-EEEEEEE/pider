<?php
namespace Pider\Support;

use Pider\Spider;

class SpiderWise {

    private static $wait_queue = [];
    private static $spiders = [];

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

    public static function linkSpider(string $url = '') {
        $linked_spiders = [];
        $specific_spiders = [];
        if (!empty($url)) {
            $domain = parse_url($url,PHP_URL_HOST);
        }
        $spiders = self::listSpider(APP_ROOT.'/examples/company');
        foreach($spiders as $spider) {
            include_once(APP_ROOT.'/examples/company/'.$spider.'.php');
            $spider_obj = new $spider;
            self::$spiders[$spider] = $spider_obj;
            $domains = $spider_obj->getDomains();
            $linked_spiders[$spider]  = $domains;
            if (!empty($url)) {
                $match_domain = (is_array($domains) && in_array($domain,$domains)) || (is_string($domains) && $domains == $domain);
                if($match_domain) {
                    $specific_spiders [] = $spider;
                }
            }
        }
        if (!empty($url)) {
            return $specific_spiders;
        } else {
            return $linked_spiders;
        }
    }


    /**
     * @method dispatchSpider()
     *
     * Dispense url to spiders
     */
    public static function dispatchSpider($url, int $size) {
        $spiders = self::linkSpider($url);
        foreach($spiders as $spider) {
            if(empty(self::$wait_queue[$spider]) || count(self::$wait_queue[$spider]) < $size  || !in_array($url,self::$wait_queue[$spider]))  {
                    self::$wait_queue[$spider][] = $url;
                    array_unique(self::$wait_queue[$spider]);
            } 
            if (!empty(self::$wait_queue[$spider]) && count(self::$wait_queue[$spider]) >= $size){
               $spider_obj = new $spider();
               $spider_obj->fromUrls(self::$wait_queue[$spider]);
               $spider_obj->go();
               //clean wait_queue
               self::clear_queue($spider);
            }
        }
    }

    private static function clear_queue(string $spider) {
        self::$wait_queue[$spider] = [];
    }

} 

