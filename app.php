<?php

require_once('Extension/autoloader.php');
date_default_timezone_set('Asia/Shanghai');
define("APP_ROOT",dirname(__FILE__));
//Compatible with phpspider's log system,fuck
define('PATH_DATA',APP_ROOT.'/cache/');
autoloader::autoload();



