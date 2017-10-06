<?php
include_once("./StaticEnvExtendstest.php");
class StaticTestWrapper extends Test{
    public function __construct() {
        echo "Static Test Wrapper \n";
    }
    public function get_env_var($var) {
        return $this->env_vars[$var];
    }
    public function set_env_var($var,$value) {
        $this->env_vars[$var]  = $value;
    }
}
