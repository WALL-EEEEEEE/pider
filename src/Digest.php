<?php
/**
 * @class Digest 
 * Digest provieds serveral tools for facilitating post-process and analysis of collected data , such as data-filter, well database interaction and so on.
 * */
namespace Pider;

use Pider\Kernel\Kernel;
use Pider\Kernel\WithKernel;
use Pider\Kernel\MetaStream;
use Pider\Kernel\Stream;
use Pider\Kernel\WithStream;
use Pider\Config;


abstract class Digest {

    private static $kernel;
    protected static $Configs;

    public function __construct() {
        $this->kernelize();
    }

    public function kernelize() {
        if(empty(self::$kernel)) {
            self::$kernel = new Kernel();
        }
        $kernel = self::$kernel;
        //init configs for spider
        self::$Configs = Config::copy($kernel->Configs);
        self::$Configs->setAsGlobal();
    }

    /**
     * @method process
     * process data in here
     */
    public abstract function process();
}
