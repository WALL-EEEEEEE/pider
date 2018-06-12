<?php
require_once('Pider.php');
use Twig\Pollen\Protocols\Osci_v1 as Osci;
use Twig\Pollen\Oscillated;
use Twig\Pollen\Oscillate;
use Twig\Process\Processd   as Processd;
use Twig\Process\Process    as Process;
use Pider\Support\URLCenter as URLCenter;
use Pider\Extension\autoloader;
use Pider\Support\SpiderWise;
use Pider\Kernel\Kernel;
use Pider\Config;



/**
 * @class Piderd
 *
 * Support distributed facilities
 */

class Piderd {

    private static $configs;
    private static $ucenter;
    private static $kernel;
    /**
     * @method runAsServer()
     * Start a Osci server as sever end
     */
    public static function runAsServer() {
        self::__init();
        $pm = new Processd('Piderd');
        $ucenter = self::$ucenter;
        $process = new Process(function() use ($ucenter){
            $osci_server = new Oscillated('127.0.0.1', 1180);
            $osci_server->on('CREATE',function() use (&$ucenter) {
                $ucenter->init();
            });
            $osci_server->on('GET_URL_FILTER',function($connection, $domain) use ($ucenter) {
                return $ucenter->getOne().PHP_EOL;
            });
            $osci_server->on('GET_URL',function() use ($ucenter){
                return $ucenter->getOne().PHP_EOL;
            });
            $osci_server->on('PUT_URL',function($connection,$url) use($ucenter){
                $ucenter->putOne($url);
            });
            $osci_server->listen();
        },'Piderd[Osci]');
        $pm->add($process);
        $pm->run();
    }

    /**
     *@method runAsClient() 
     *
     * Start a Osci client as client end
     */
    public static function runAsClient() {
        self::__init();
        while(true) {
            $osci_client = new Oscillate('127.0.0.1',1180);
            $url = $osci_client->get_url();
            if (!empty($url)) {
                SpiderWise::dispatchSpider($url,100);
            } else {
                sleep(1);
            }

        }
    }

    /**
     *@method __init()
     * 
     * Initialization
     */
    public static function __init() {
        if(empty(self::$kernel)) {
            self::$kernel = new Kernel();
        }
        $kernel = self::$kernel;
        //init configs for spider
        self::$configs = Config::copy($kernel->Configs);
        self::$configs->setAsGlobal();
        self::$ucenter = new URLCenter();
        if (!empty(self::$configs['URLCenter'])) {
            $directory = APP_ROOT.'/'.self::$configs['URLCenter'];
            if(is_dir($directory) && file_exists($directory)) {
                autoloader::register($directory);
                $sources = scandir($directory);
                foreach($sources as $source) {
                    if(!is_dir($source) && pathinfo($source,PATHINFO_EXTENSION)) {
                        $source_cls = pathinfo($source,PATHINFO_FILENAME); 
                        include_once($source_cls.'.php');
                        $source_obj =  new $source_cls();
                        self::$ucenter->addSource($source_obj);
                    }
                }
            } else {
                        throw new \ErrorException("Invalid directory ".$dirctory." for URLCenter");
            }
        } else {
                        throw new \ErrorException("Please configure URLCenter in your config at first !");
        }
    }
}
