<?php
namespace Pider\Kernel\Core;

/**
 * @class ModuleLoader
 * Load and register module into kernel
 */
class ModuleLoader {
    private $modules = [];
    private const DEFAULT_MODULE_PATH= "Module";

    public function __invoke() {
        return $this->init();
    }
    public function init() {
        $this->load();
    }
    public function load() {
        $module_path = PIDER_PATH.'/'.self::DEFAULT_MODULE_PATH;
    }
}

