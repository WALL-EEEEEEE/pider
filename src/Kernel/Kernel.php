<?php
namespace Pider\Kernel;

/**
 * @class Pider\Kernel\Kernel 
 *
 * Kernel class is core class for scheduling tasks and resources, for example:
 * module load, and render different tasks from Spider to 
 * specified modules and middlewares 
 */
use Pider\Kernel\Config;
use Pider\Kernel\MetaStream;
use Pider\Kernel\Stream;
use Pider\Kernel\StreamInvalid;

class Kernel implements WithStream {
    private $cores= [];
    private $extras= [];
    private $actived = [];
    private $attaches = [];
    private $streams = [];
    private $KernelConfigs;
    public $Configs;


    public final function __construct() {
        $this->Configs =  (new Config())();
        $config = $this->Configs->KernelConfig();
        $this->cores = $config->Cores;
        $this->extras = $config->Extras;
        $this->init();
    }

    /**
     * @method init()
     * Load kernel modules  
     */
    private function init() {
        //load core kenel modules
        $this->LoadCores();
        $this->LoadExtras();
    }

    /**
     * @method CoreKernelModules()
     * Load core kenel modules
     *
     */
    private function LoadCores() {
        try {
            $cores = $this->cores;
            foreach($cores as $core) {
                $module = (new $core())();
                if (!empty($module)) {
                    $this->actived = array_merge($this->actived,$module);
                }
            }
        } catch(ErrorException  $exception) {
            throw new KernelError("Kernel Error: When init module ".$core);
        }
    }

    /**
     * @method ExtraKernelModules()
     * Load extra kernel modules
     */
    private function LoadExtras() {
        try {
            $extras = $this->extras;
            foreach($extras as $extra) {
                $module = new $extra();
                $this->actived = array_merge($this->actived, $module());
            }
        } catch(ErrorException  $exception) {
            throw new KernelException("Kernel Exception in module ".$extra);
        }
    }

    /**
     * @method dispatch
     *
     * Dispatch Streams to different modules or handles
     */
    private function dispatch() {
        foreach ($this->streams as $stream) {
            foreach($this->actived as $module) {
                if ($module->isStream($stream)) {
                    $fromstream = $module->fromStream($stream,$this);
                }
            }
        }
    }

    /**
     * @method fromStream()
     * Accepted stream from other components, Stream is just like a resource request      */
    public function fromStream(Stream $stream, WithStream $fromObject) {
        if ($stream instanceof MetaStream) {
            $this->streams[] = $stream;
        } else {
            throw new StreamInvalid("Invalid stream provided!");
        }
    }

    public function toStream() {
        $this->dispatch();
    }
    public function isStream(Stream $stream) {
        if($stream instanceof MetaStream ) {
            return true;
        }
        return false;
    }

    public function __get(string $attachname) {
        if(array_key_exists($attachname,$this->attaches)) {
            return $this->attaches[$attachname];
        } else {
            return '';
        }
    }
    public function __set(string $attachname ,WithStream $attach) {
        $this->attaches[$attachname] = $attach;
    }
}
