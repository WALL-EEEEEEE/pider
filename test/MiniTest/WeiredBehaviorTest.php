<?php
include_once('../../Extension/Controller.php');
include_once('../../Controller/WebsiteController.php');
use Controller\WebsiteController;
error_reporting(E_ALL);

class WeiredBehavior {
    public function __construct() {
        printf("%s\n","I'll be executed");
        $website = new WebsiteController(3);
        printf("%s\n","I'll never be executed");
    }
}
$class = new WeiredBehavior();

