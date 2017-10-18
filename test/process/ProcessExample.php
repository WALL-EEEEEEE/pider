<?php
//limit the task to be forked
$task = 10;
$process_pool = array();
//allocate 1kb memory segment to store process_pool
$process_pool_key = ftok(__FILE__,'0');
$process_pool_shm = shmop_open($process_pool_key,'c',0644,1024);

for($i = 1; $i<= 10; $i++) {
    $pid = pcntl_fork();
    if ($pid == -1) {
        die("Can't fork child process.");
    }
    if ($pid == 0) {
        $current_pid = getmypid();
        echo "Current pid: \n";
        var_dump($current_pid);
        //Get current processid set
        $process_pool = unserialize(shmop_read($process_pool_shm,0,1024));
        echo "Process pool: \n";
        var_dump(in_array($current_pid,array_keys($process_pool)));
        sleep(30);
        exit(0);
    } else {
       $process_pool[$pid] = array($pid);
       shmop_write($process_pool_shm,serialize($process_pool),0);
    }
}
