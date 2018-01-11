<?php

namespace Pider\Kernel;

/**
 * @class Pider\Kernel\Config
 * 
 * Load kernel configs 
 *
 */

class Config {

    private $Configs = [];
    private const DEFAULT_CONFIG_PATH = 'Config/kernel.config.php';

    public function __invoke() {
        $this->load();
        return $this;
    }
    public function __get(string $kconfig) {
        if(in_array($kconfig, $this->Configs)) {
            return $this->Configs[$kconfig];
        } else {
            return '';
        }
    }
    public function __set(string $kconfig, $vconfig) {
        $this->Configs[$kconfig] = $vconfig;
    }
    public function load() {
        $Configs = include_once(self::DEFAULT_CONFIG_PATH);
        $this->Configs = $Configs;
    }
}
