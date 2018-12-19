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
use Pider\Kernel\Event\Dispenser;
use Pider\Kernel\Event\Listener;
use Pider\Kernel\Event\Event;
use Pider\Log\Log as Logger;

class Kernel implements WithStream {
    use Dispenser;
    use Listener;

    private $cores= [];
    private $components = [];
    private $actived = [];
    private $attaches = [];
    private $streams = [];
    private $events = [
        'SPIDER_START',
        'SPIDER_CLOSE',
    ];
    private $KernelConfigs;
    public  $Configs;
    private static $logger;

    public final function __construct() {
        self::$logger = Logger::getLogger();
        $logger = self::$logger;
        $logger->debug('Load Config ... ');
        $this->Configs =  (new Config())();
        $config = $this->Configs->KernelConfig();
        $logger->debug('Load Config ... done');
        $this->cores = $config->Cores;
        $logger->debug("Load components ...");
        $this->components = $config->Components;
        $this->init();
        $logger->debug("Load components ... done");
        $logger->debug("Actived components:");
        $logger->debug($this->getComponentsInfo());
        $this->dispense(new Event('SPIDER_START'));

    }

    /**
     * @method init()
     * Load kernel modules  
     */
    private function init() {
        //load core kenel modules
        $this->LoadCores();
        $this->LoadComponents();
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
                $module = (new $core())($this);
                if (!empty($module)) {
                    if(is_array($module)) {
                        $this->actived = array_merge($this->actived,$module);
                    } else {
                        $this->actived[] = $module;
                    }
                }
            }
        } catch(ErrorException  $exception) {
            throw new KernelError("Kernel Error: When init module ".$core);
        }
    }

    /**
     * @method LoadComponents()
     * Load kernel components 
     */
    private function LoadComponents() {
            $components = $this->components;
            foreach($components as $component) {
                try {
                    $instance = new $component();
                    if (!empty($instance)) {
                        if (is_array($instance($this))) {
                            $this->actived = array_merge($this->actived, $instance($this));
                        } else {
                            $this->actived[] = $instance($this);
                        }
                    }
                } catch(ErrorException  $exception) {
                    throw new KernelException("Kernel Exception in component ".$component);
                }
            }
        
    }
    
    /**
     * @method getComponentsInfo()
     * get components info in string format
     */
    private function getComponentsInfo():string {
       $components_info = '<'.implode(',',$this->components).'>';
       return $components_info;
    }
    /**
     * @method dispatch
     *
     * Dispatch Streams to different modules or handles
     */
    private function dispatch() {
        $this->actived = array_merge($this->actived,$this->attaches);
        while(!empty($this->streams)) {
            $stream = array_shift($this->streams);
            foreach($this->actived as $module) {
                if ($module->isStream($stream)) {
                    $module->fromStream($stream,$this);
                    $tostream = $module->toStream();
                    if (!empty($tostream) && $this->isStream($tostream) && $tostream->type() !== "FINISHED" ) {
                        if ($stream->type() !== $tostream->type()) {
                            array_unshift($this->streams, $tostream);
                        } else {
                            $stream = $tostream;
                        }
                    }
                }
            }
        }
        $logger = self::$logger;
        $logger->debug("Spider closed");
        $this->dispense(new Event('SPIDER_CLOSE'));
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


    public function pushStream(Stream $stream, WithStream $fromObject) {
        if ($stream instanceof MetaStream) {
            array_unshift($this->streams,$stream);
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
