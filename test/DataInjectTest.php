<?php
include_once('../app.php');
use Module\Data\DI\GrapeCategoryDI;

$grape_category = (new GrapeCategoryDI())();
var_dump($grape_category);

