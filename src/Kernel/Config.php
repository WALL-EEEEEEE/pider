<?php

namespace Pider\Kernel;

/**
 * @class Pider\Kernel\Config
 * 
 * Load kernel configs 
 */

class Config implements \ArrayAccess {

    private $Configs = [];
    private static $KernelConfigs = [];
    private static $ProjectConfigs = [];
    private static $FrameworkConfigs = [];
    private const DEFAULT_CONFIG_PATH = 'Config/kernel.config.php';
    private const DEFAULT_FRAMEWORK_CONFIG_PATH = 'Config/config.php';
    private const DEFAULT_PROJECT_CONFIG_PATH = 'Config/config.php';

    public function __construct() {
        $this->load();
    }

    public function __invoke() {
        $config = new Config();
        $config->Configs = $this->Configs;
        return $config;
    }

    public function KernelConfig() {
        $config = new Config();
        $config->Configs = self::$KernelConfigs;
        return $config;
    }
    public function __get(string $kconfig) {
        if(array_key_exists($kconfig, $this->Configs)) {
            return $this->Configs[$kconfig];
        } else {
            return '';
        }
    }
    public function __set(string $kconfig, $vconfig) {
        $this->Configs[$kconfig] = $vconfig;
    }
    public function load() {
        self::$KernelConfigs = !empty(self::$KernelConfigs)?self::$KernelConfigs:include_once(self::DEFAULT_CONFIG_PATH);
        self::$FrameworkConfigs = !empty(self::$FrameworkConfigs)?self::$FrameworkConfigs:include_once(PIDER_PATH.DIRECTORY_SEPARATOR.self::DEFAULT_FRAMEWORK_CONFIG_PATH);
        self::$ProjectConfigs = !empty(self::$ProjectConfigs)?self::$ProjectConfigs:include_once(APP_ROOT.DIRECTORY_SEPARATOR.self::DEFAULT_PROJECT_CONFIG_PATH);
        if (!is_array(self::$KernelConfigs)) {
            throw new ConfigError("Invalid kernel config format!");
        }
        if (!is_array(self::$FrameworkConfigs)) {
            throw new ConfigError("Invalid framework config format!");
        }
        if (!is_array(self::$ProjectConfigs)) {
            throw new ConfigError("Invalid project config format!");
        }
        $this->Configs = array_merge(self::$FrameworkConfigs,self::$ProjectConfigs);
    }

    /**
     * @method offsetExist
     * implement from ArrayAccess
     *
     */
    public function offsetExists($offset) {
        return isset($this->Configs[$offset]);
    }

    /**
     * @method offsetSet
     * implement from ArrayAccess
     */

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->Configs[] = $value;
        } else {
            $this->Configs[$offset] = $value;
        }
    }

    /**
     * @method offsetUnset
     * implement from ArrayAccess
     */
    public function offsetUnset($offset) {
        unset($this->Configs[$offset]);
    }

    /**
     * @method offsetGet
     * implement from ArrayAccess
     *
     */
    public function offsetGet($offset) {
        return isset($this->Configs[$offset]) ? $this->Configs[$offset] : null;
    }
}
