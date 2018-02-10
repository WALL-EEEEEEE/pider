<?php
namespace Pider\Kernel\Core;

/**
 * @class ModuleLoader
 * Load and register module into kernel
 */
class ModuleLoader {
    private $modules = [];
    private const DEFAULT_MODULE_LOCATION= "Module";
    private const DEFAULT_NAMESPACE_PREFIX = "Pider\\Module\\";

    public function __invoke() {
        return $this->init();
    }
    public function init() {
        $this->load();
        return $this->modules;
    }
    public function load() {
        $modules_path = PIDER_PATH.'/'.self::DEFAULT_MODULE_LOCATION;
        $dirs = scandir($modules_path);
        $classes = [];
        foreach($dirs as $dir) {
            if (!is_dir($dir) && pathinfo($dir,PATHINFO_EXTENSION) == "php") {
                $classname = pathinfo($dir,PATHINFO_FILENAME);
                $fclassname = self::DEFAULT_NAMESPACE_PREFIX.$classname;
                $this->modules[] = new $fclassname();
            }
        }

    }
}

