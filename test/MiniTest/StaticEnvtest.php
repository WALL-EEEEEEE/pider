<?php
class Env {
    protected static $vars = [];
    public static function get_env_var($var) {
        return self::$vars[$var];
    }
    public static function  set_env_var($var,$value) {
        self::$var[$var] = $value;
    }
}
