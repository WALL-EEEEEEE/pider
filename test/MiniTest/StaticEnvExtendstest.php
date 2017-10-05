<?php
include_once('./StaticEnvtest.php');
class Test extends Env {
    private $env_vars = [];
    public function __construct() {
        $this->env_vars = self::$vars;
    }
}
$Test = new Test();
