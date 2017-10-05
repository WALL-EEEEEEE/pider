<?php
namespace UnitTest;
include_once('../../app.php');
use Extension\DBExtensionExtendsExample;
var_dump("Before initialized");
$class= new DBExtensionExtendsExample();
var_dump("After initialized");
