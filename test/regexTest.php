<?php

$string = "满199减100";

preg_match('/(满\d+减\d+)/i',$string,$matches);
var_dump($matches);

