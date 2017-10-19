<?php
//limit the task to be forked
$task = 100;
$process_pool = array();
//allocate 1kb memory segment to store process_pool
$process_pool_key = ftok(__FILE__,chr(0));
$process_pool_shm = shmop_open($process_pool_key,'c',0644,1024);
$datas = array();

for($i = 1; $i<= $task; $i++) {
    $pid = pcntl_fork();
    if ($pid == -1) {
        die("Can't fork child process.");
    }
    if ($pid == 0) {
        $current_pid = getmypid();
        $process_pool_size = shmop_size($process_pool_shm);
        $process_pool = @unserialize(shmop_read($process_pool_shm,0,$process_pool_size));
        //store child process data into specific memory
        $child_data  = array('pid'=>$current_pid,"data"=>[rand(),'hello']);
        $child_key = ftok(__FILE__,chr($current_pid));
        $size = 1024*1024; 
        $child_shm = shmop_open($child_key,'c',0644,$size);
        shmop_write($child_shm,serialize($child_data),0);
        exit(0);
    } else {
       $process_pool[$pid] = array($pid);
       shmop_write($process_pool_shm,serialize($process_pool),0);
   }
}

while(pcntl_waitpid(-1,$status) > 0);
//Read data from all child process
foreach($process_pool as $pid => $pid_info) {
    $tmp_key = ftok(__FILE__,chr($pid));
    $size= 1024*1024;;
    $tmp_shm  = shmop_open($tmp_key,'a',0644,$size);
    $org_data = shmop_read($tmp_shm,0,$size);
    $child_data = @unserialize($org_data);
    if (empty($child_data)) {
        echo "$tmp_key\n";
    }
    shmop_delete($tmp_shm);
    shmop_close($tmp_shm);
    if (!empty($child_data)) {
        $datas[$pid] = $child_data;
    }
}
var_dump(count($datas));
var_dump(count($process_pool));
var_dump(count(array_keys($datas)));
var_dump(count(array_keys($process_pool)));
foreach (array_keys($process_pool) as $p_key) {
    if (!in_array($p_key,array_keys($datas))) {
        echo $p_key."\n";
    }
}
shmop_delete($process_pool_shm);
shmop_close($process_pool_shm);
