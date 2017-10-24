<?php
$process = new swoole_process('callback_function');
$pid = $process->start();
function callback_function(swoole_process $worker) 
{
    $worker->exec('/usr/bin/php',array(__DIR__.'/server.php'));
}
swoole_process::wait();
