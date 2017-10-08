<?php
$str='中秋特惠';
$regex = '/(中秋|国庆)/';
var_dump(preg_match($regex,$str,$matches));
var_dump($matches);

