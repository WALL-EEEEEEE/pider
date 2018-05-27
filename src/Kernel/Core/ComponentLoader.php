<?php
namespace Pider\Kernel\Core;

use Pider\Kernel\Kernel as Kernel;

/**
 * Load and register component into kernel
 */
class ComponentLoader {
    private $components = [];
    private $kernel;
    private const DEFAULT_COMPONENTS_LOCATION = "Component";
    private const DEFAULT_NAMESPACE_PREFIX    ="Pider\\Component\\";

    public function __invoke(Kernel $kernel) {
        $this->kernel = $kernel;
        $this->init();
    }
    public function init() {
       $this->load();
    }
    public function load() {
        $components_path = PIDER_PATH.'/'.self::DEFAULT_COMPONENTS_LOCATION;
        $dirs = scandir($components_path);
        $classes = [];
        foreach($dirs as $dir) {
            if (!is_dir($dir) && pathinfo($dir,PATHINFO_EXTENSION) == "php") {
                $classname = pathinfo($dir,PATHINFO_FILENAME);
                $fclassname = self::DEFAULT_NAMESPACE_PREFIX.$classname;
                $component =  new $fclassname($this->kernel);
                $component->init();
                $this->components[] = $component;
            }
        }
    }
}


