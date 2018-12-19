<?php
namespace Pider;

use Pider\Kernel\Config as BaseConfig;
use Pider\Kernel\ConfigError as ConfigError;
use Pider\Exceptions\ConfigNotFoundException as ConfigNotFoundException;

class Config implements \ArrayAccess {

   private $Configs = [];
   private static $instance;

   /**
    * @method fromArray()
    * Construct Config class from from array
    *
    * @param Array $configs  config array to be loaded.
    * @return Config  A new Config class
    */
   public static function fromArray(array $configs = []) {
       $config = new Config();
       $config->Configs = $configs;
       return $config;
   }

   /**
    * @method fromFile()
    * Construct Config class from config file
    * @param string $filename config filename to be read  
    * @return  Config  A new Config class
    */
   public static function fromFile(string $filename) {

       if (!file_exists($filename)) {
           throw new ConfigNotFoundException("Config file ".$filename.' not found, check you path carefully!');
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

   /**
    *@method copy()
    * 
    * Construct Config class from copying
    *
    * @param Config  Config class to be copied
    * @return Config A new config class
    *
    */
   public static function copy(BaseConfig $extra_config ) {
       $config = new Config();
       $extra_configs = array_values((array) $extra_config);
       $config->Configs  = @$extra_configs[0];
       return $config;
   }

   /**
    * @method setAsGlobal()
    *
    * Expose Config class to global scope
    *
    */
   public function setAsGlobal() {
       self::$instance = $this;
   }

   /**
    * @method unsetAsGlobal()
    *
    * Revoke Config class from global scope 
    */
   public static function unsetAsGlobal() {
       self::$instance = '';
   }

   /**
    * @method get()
    *
    * Implement from ArrayAccess
    *
    */
   public static function get($ckey) {
       return self::$instance->$ckey;
   }

   /**
    * @method set()
    * Implement from ArrayAccess
    */
   public static function set($ckey, $cvalue) {
       self::$instance->$ckey = $cvalue;
   }

   /**
    * @method toArray()
    *
    * Convert Config class to Array
    *
    */
   public function toArray() {
       return $this->Configs;
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
     * @method offsetExist()
     * implement from ArrayAccess
     *
     */
    public function offsetExists($offset) {
        return isset($this->Configs[$offset]);
    }

    /**
     * @method offsetSet()
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
     * @method offsetUnset()
     * implement from ArrayAccess
     */
    public function offsetUnset($offset) {
        unset($this->Configs[$offset]);
    }

    /**
     * @method offsetGet()
     * implement from ArrayAccess
     *
     */
    public function offsetGet($offset) {
        return isset($this->Configs[$offset]) ? $this->Configs[$offset] : null;
    }
}


