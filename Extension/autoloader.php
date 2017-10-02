<?php
/**
 * 
 * This file is  used to autoload class depend by spl_auto_load
 *
 */
class autoloader {

    private $extras = array();

    public static function autoload() {
       $autoloader = new autoloader();
       #Load the config at first
       $config_path = APP_ROOT.'/phpspider/config/inc_config.php';
       include_once($config_path);
       //The common function sets,usefull but not be orgnized well now.
       include_once(APP_ROOT.'/Util/Common.php');
       spl_autoload_register(array($autoloader,'loadCore'));
       spl_autoload_register(array($autoloader,'loadModule'));
       spl_autoload_register(array($autoloader,'loadExt'));
       spl_autoload_register(array($autoloader,'loadModel'));
       spl_autoload_register(array($autoloader,'loadUtil'));
       spl_autoload_register(array($autoloader,'loadUtil'));
       spl_autoload_register(array($autoloader,'loadController'));
    }

    public static function register($name,$path) {

    }

    private function loadModule($class) {
        $module_path = APP_ROOT;
        set_include_path(get_include_path().PATH_SEPARATOR.$module_path);
        $class = str_replace('\\','/',$class);
        @include_once($module_path.'/'.$class.'.php');
    }

    private function loadExt($class) {
        $ext_path = APP_ROOT;
        set_include_path(get_include_path().PATH_SEPARATOR.$ext_path);
        $class = str_replace('\\','/',$class);
        @include_once($ext_path.'/'.$class.'.php');
    }
    private function loadModel($class) {
        $model_path = APP_ROOT;
        set_include_path(get_include_path().PATH_SEPARATOR.$model_path);
        $class = str_replace('\\','/',$class);
        @include_once($model_path.'/'.$class.'.php');

    }
    private function loadUtil($class) {
        $util_path = APP_ROOT;
        set_include_path(get_include_path().PATH_SEPARATOR.$util_path);
        $class = str_replace('\\','/',$class);
        @include_once($util_path.'/'.$class.'.php');


    }
    private function loadController($class) {
        $controller_path = APP_ROOT;
        set_include_path(get_include_path().PATH_SEPARATOR.$controller_path);
        $class = str_replace('\\','/',$class);
        @include_once($controller_path.'/'.$class.'.php');
    }
    private function loadCore($class) {
        $core_path = APP_ROOT.'/phpspider/core/';
        $library_path =APP_ROOT.'/phpspider/library/';
       set_include_path(get_include_path().PATH_SEPARATOR.$core_path.PATH_SEPARATOR.$library_path);
        spl_autoload_extensions('.php');
        spl_autoload($class);
    }
}
