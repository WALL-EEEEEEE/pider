<?php
$process = new swoole_process(function (swoole_process $process){
    $process->write('Hello');
},true);
$process->start();
usleep(100);
echo $process->read(); //echo Hello
