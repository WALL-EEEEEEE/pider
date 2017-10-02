<?php
include_once("./Controller.php");
use MiniTest\Controller;
class TestController extends Controller {
    public function __construct() {
        printf("%s\n","TestController");
    }
}
