<?php
namespace Pider;

use Pider\Kernel\Config as BaseConfig;
use Pider\Kernel\ConfigError as ConfigError;
use Pider\Exceptions\FileNotFoundException;

class Config implements \ArrayAccess {

   private $Configs = [];
   private static $instance;

   public static function fromArray(array $configs = []) {
       $config = new Config();
       $config->Configs = $configs;
       return $config;
   }

   public static function fromFile(string $filename) {

       if (!file_exists($filename)) {
           throw FileNotFoundException("Config file ".$filename.' not found, check you path carefully!');
       } else {
           $configs = include($filename);
           if (!is_array($configs)) {
               throw new ConfigError('Config format error in '.$filename);
           }
       }
       $config = new Config();
       $config->Configs = $configs;
       return $config;
   }

   public static function copy(BaseConfig $extra_config ) {
       $config = new Config();
       $extra_configs = array_values((array) $extra_config);
       $config->Configs  = @$extra_configs[0];
       return $config;
   }

   public function setAsGlobal() {
       self::$instance = $this;
   }

   public static function get($ckey) {
       return self::$instance->$ckey;
   }

   public static function set($ckey, $cvalue) {
       self::$instance->$ckey = $cvalue;
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


