<?php
include_once('../app.php');
use Model\UrltagModel;
use Extension\DBExtension;
$GLOBALS['website']['id'] = 1;
DBExtension::switch_db('phpspider');

$tagmodel = new UrltagModel($GLOBALS['website']['id']);
$response = $tagmodel->prune_yesterday();
var_dump($response);
$tagmodel2 = new UrltagModel();
$response2 = $tagmodel2->prune_yesterday();
var_dump($response2);

