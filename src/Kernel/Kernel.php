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

class Kernel {
    private $CoreModules = [];
    private $ExtraModules = [];
    private $ActivedModules = [];
    private $Configs;


    public final function __construct() {
        $this->Configs = (new Config())();
        $this->CoreModules = $this->Configs->Modules['Cores'];
        $this->ExtraModules = $this->Configs->Modules['Extras'];
        $this->init();
    }

    /**
     * @method init()
     * Load kernel modules  
     */
    private function init() {
        //load core kenel modules
        $this->CoreKernelModules();
        $this->ExtraKernelModules();
    }

    /**
     * @method CoreKernelModules()
     * Load core kenel modules
     *
     */
    private function CoreKernelModules() {
        try {
            $cores = $this->CoreModules;
            foreach($cores as $core) {
                $module = new $core();
                $this->ActivedModules[] = $module();
            }
        } catch(ErrorException  $exception) {
            throw new KernelError("Kernel Error: When init module ".$core);
        }
    }

    /**
     * @method ExtraKernelModules()
     * Load extra kernel modules
     */
    private function ExtraKernelModules() {
        try {
            $extras = $this->ExtraModules;
            foreach($extras as $extra) {
                $module = new $extra();
                $this->ActivedModules[] = $module();
            }
        } catch(ErrorException  $exception) {
            throw new KernelException("Kernel Exception in module ".$extra);
        }
    }
}
