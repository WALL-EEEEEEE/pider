<?php
include_once('./MalFormNamespaceBehavior.php');
use MiniTest\Contrller;
class InstanceBehavior extends Controller{
    public function __construct() {
        echo "InstanceBehavior ....";
    }
}
$class = new InstanceBehavior();


