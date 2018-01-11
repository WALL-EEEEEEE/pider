<?php
error_reporting(E_ALL);
require_once('vendor/autoload.php');
require_once('Extension/autoloader.php');
date_default_timezone_set('Asia/Shanghai');
defined('FRAMEWORK_NAME')?'':define('FRAMEWORK_NAME','Pider');
defined('APP_ROOT')?'':define('APP_ROOT',dirname(__DIR__,1));
defined('PIDER_PATH')?'':define('PIDER_PATH',dirname(__FILE__));
autoloader::autoload();




