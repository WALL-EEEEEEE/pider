<?php
namespace Pider\Extension;
/**
 * 
 * This file is  used to autoload class depend by spl_auto_load
 *
 */
class autoloader {

    private $extras = array();

    public static function autoload() {
       $autoloader = new autoloader();
       set_include_path(get_include_path().PATH_SEPARATOR.APP_ROOT);
       set_include_path(get_include_path().PATH_SEPARATOR.PIDER_PATH);
       set_include_path(get_include_path().PATH_SEPARATOR.APP_ROOT.DIRECTORY_SEPARATOR.'Component');
       #Load the config at first
       spl_autoload_register(array($autoloader,'loadCore'));
       spl_autoload_register(array($autoloader,'loadModule'));
       spl_autoload_register(array($autoloader,'loadExt'));
       spl_autoload_register(array($autoloader,'loadModel'));
       spl_autoload_register(array($autoloader,'loadUtil'));
       spl_autoload_register(array($autoloader,'loadController'));
       spl_autoload_register(array($autoloader,'loadExtern'));
    }

    public static function register(string $path) {
        set_include_path(get_include_path().PATH_SEPARATOR.$path);
    }
 
    private function loadModule($class) {
        $module_path = APP_ROOT;
        $class = str_replace('\\','/',$class);
       if (stripos($class,"Module") !== false && file_exists($module_path.'/'.$class.'.php')) {
            include_once($module_path.'/'.$class.'.php');
        }
    }

    private function loadExt($class) {
        $ext_path = APP_ROOT;
        $class = str_replace('\\','/',$class);
       if (stripos($class,"Extension") !== false && file_exists($ext_path.'/'.$class.'.php')) {
            include_once($ext_path.'/'.$class.'.php');
        }
    }
    private function loadModel($class) {
        $model_path = APP_ROOT;
        $class = str_replace('\\','/',$class);
        if (stripos($class,"Model") !== false && file_exists($model_path.'/'.$class.'.php')) {
            include_once($model_path.'/'.$class.'.php');
        }
    }
    private function loadUtil($class) {
        $util_path = APP_ROOT;
        $class = str_replace('\\','/',$class);
        if (stripos($class,"Util") !== false && file_exists($util_path.'/'.$class.'.php')) {
            include_once($util_path.'/'.$class.'.php');
        }
    }
    private function loadController($class) {
        $controller_path = APP_ROOT;
        $class = str_replace('\\','/',$class);
        if (stripos($class,'Controller') !== false && file_exists($controller_path.'/'.$class.'.php')) {
            include_once($controller_path.'/'.$class.'.php');
        }
    }
    private function loadCore($class) {
        $classpath =  str_replace('\\',DIRECTORY_SEPARATOR,$class);
        $real_classpath = '';
        if (strpos($classpath,FRAMEWORK_NAME) !== false) {
            $real_classpath = substr($classpath,strpos($classpath,FRAMEWORK_NAME)+strlen(FRAMEWORK_NAME)+1);
            include_once($real_classpath.'.php');
        }
    }

    /**
     * @method loadExtern
     * Load and register external framework or library 
     *
     * Note: `src/autoload.php` file will be read and included, so third-party framework or library should maintance their own dependencies load logics in `src/autoload.php`. 
     *
     */
    private function loadExtern($class) {
        $classpath =  str_replace('\\',DIRECTORY_SEPARATOR,$class);
        if (strpos($classpath,FRAMEWORK_NAME) === false) {
            //get third-party library name
            $libname = substr($classpath,0,strpos($classpath,DIRECTORY_SEPARATOR));
            //locate autoload.php in libname/src/autoload.php
            $depfile = PIDER_PATH.DIRECTORY_SEPARATOR.$libname.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'autoload.php';
            if(file_exists($depfile)) {
                include_once($depfile);
            }
        }
    }
}
