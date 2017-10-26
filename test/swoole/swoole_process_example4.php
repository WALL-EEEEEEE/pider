<?php
$workers = [];
$worker_num = 3;

for($i = 0; $i < $worker_num; $i++){
    $process = new swoole_process('process');
    $pid = $process->start();
    $workers[$pid] = $process;
}
foreach($workers as $process) {
    swoole_event_add($process->pipe,function($pipe) use ($process) {
        $data = $process->read();
        echo "RECV:".$data.PHP_EOL;
    });
}

function process(swoole_process $process) {
    $process->write($process->pid);
    echo $process->pid,"\t",$process->callback.PHP_EOL;
}
