<?php
namespace Pider\Support;

use Pider\Spider;
use Pider\Config;

class SpiderWise {

    private static $wait_queue = [];
    private static $spiders = [];

    public static function isSpider(string $class,$directory='') {
        @include_once($class);
        $cls = pathinfo($class,PATHINFO_FILENAME); 
        $pcls = @class_parents($cls,false);
        if (class_exists($cls,false) && in_array('Pider\Spider',$pcls)) {
            return true;
        } 
        return false;
    }

    public static function listSpider(string $directory) {
        $spiders = [];
        $files = [];
        if(file_exists($directory)) {
            $files = array_diff(scandir($directory),['.','..']); 
        } 
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
        } else {
            return [];
        }
        $spiders = self::allAvailableSpiders();
       foreach($spiders as $spider) {
            @include_once($spider['locate']);
            $spider_obj = new $spider['name'];
            self::$spiders[$spider['name']] = $spider_obj;
            $domains = $spider_obj->getDomains();
            $linked_spiders[$spider['name']]  = $domains;
            if (!empty($url)) {
                $match_domain = (is_array($domains) && in_array($domain,$domains)) || (is_string($domains) && $domains == $domain);
                if($match_domain) {
                    $specific_spiders [] = $spider['name'];
                }
            }
        }
        return $specific_spiders;
    }


    /**
     * @method dispatchSpider()
     *
     * Dispense url to spiders
     */
    public static function dispatchSpider(array $urls, int $size = 10, array $extern_params = [], string $spider_appointed= '') {
        $spiders = self::allAvailableSpiders(false);
        if (!empty($spider_appointed)) {
            $spider_appointed = [$spider_appointed];
            $spiders = array_intersect($spider_appointed,$spiders);
        }
        if (!empty($urls)) {
            foreach($urls as $url) {
                foreach($spiders as $spider) {
                    $linked_spiders = self::linkSpider($url);
                    $if_enter_queue = (isset(self::$wait_queue[$spider]) || empty(self::$wait_queue[$spider]) || count(self::$wait_queue[$spider]) < $size  || !in_array($url,self::$wait_queue[$spider])) && in_array($spider,$linked_spiders);
                    if($if_enter_queue)  {
                        self::$wait_queue[$spider][] = $url;
                        array_unique(self::$wait_queue[$spider]);
                    } 
                    if (!empty(self::$wait_queue[$spider]) && count(self::$wait_queue[$spider]) >= $size){
                        $spider_obj = new $spider();
                        $spider_obj->fromUrls(self::$wait_queue[$spider],$extern_params);
                        $spider_obj->go();
                        //clean wait_queue
                        self::clear_queue($spider);
                    }
                }
            }

            //queue remanant url 
            foreach(self::$wait_queue as $spider => $urls_queue) {
                if (!empty($urls_queue)) {
                    $spider_obj = new $spider();
                    $spider_obj->fromUrls(self::$wait_queue[$spider],$extern_params);
                    $spider_obj->go();
                    self::clear_queue($spider);
                }
            }
        }

    }

    public static function allAvailableSpiders($showLocate = true) {
        $paths = self::loadSpiderPathes();
        $spiders = [];
        foreach($paths as $path) {
            $container_path = APP_ROOT.'/'.$path;
            $spider_container = self::listSpider($container_path);
            $spider_container = array_map(function($value) use ($container_path) {
                $spider_path = $container_path.'/'.$value.'.php';
                return ['name'=> $value,'locate'=>$spider_path];
            },$spider_container);
            $spiders = array_merge($spiders,$spider_container);
        }
        if ($showLocate) {
            return $spiders;
        }
        return array_column($spiders,'name');
    }

    /**
     * @method runSpider()
     * @param  spidername or a valid spider path
     * @return bool;
     */
    public static function runSpider($spider) {
        $all_spiders = self::allAvailableSpiders();
        $all_names = array_column($all_spiders,'name');
        if(empty($spider)) {
            return false;
        }
        $spider_path = '';
        if (in_array($spider,$all_names)) {
            $name_index = array_search($spider,$all_spiders);
            $spider_path = $all_spiders[$name_index]['locate'];
        } else {
            $spider_path = $spider;
        }
        $isSpider = self::isSpider($spider_path);
        $cls = pathinfo($spider_path,PATHINFO_FILENAME); 
        if ($isSpider) {
            $spider_obj = new $cls();
            $spider_obj->go();
            return true;
        } else {
            return false;
        }

    }

    private static function clear_queue(string $spider) {
        self::$wait_queue[$spider] = [];
    }

    private static function loadSpiderPathes() {
        $spiders_path = Config::get('Spiders');
        if (is_array($spiders_path)) {
            return $spiders_path;
        } else if (is_string($spiders_path)) {
            return [$spiders_path];
        }
        return [];
 
    }

} 

