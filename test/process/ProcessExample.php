<?php
//limit the task to be forked
$task = 10;
$process_pool = array();
//allocate 1kb memory segment to store process_pool
$process_pool_key = ftok(__FILE__,chr(0));
$process_pool_shm = shmop_open($process_pool_key,'c',0644,1024);
$datas = array();

for($i = 1; $i<= 10; $i++) {
    $pid = pcntl_fork();
    if ($pid == -1) {
        die("Can't fork child process.");
    }
    if ($pid == 0) {
        $current_pid = getmypid();
        $process_pool = unserialize(shmop_read($process_pool_shm,0,1024));
        //store child process data into specific memory
        $child_data  = array('pid'=>$current_pid,"data"=>[rand(),'hello']);
        var_dump($child_data);
        $child_key = ftok(__FILE__,chr($current_pid));
        $child_shm = shmop_open($child_key,'c',0644,1024);
        shmop_write($child_shm,serialize($child_data),0);
        sleep(30);
        exit(0);
    } else {
       $process_pool[$pid] = array($pid);
       shmop_write($process_pool_shm,serialize($process_pool),0);

   }
}

if (pcntl_waitpid(-1,$status)) {
    //Read data from all child process
    foreach($process_pool as $pid => $pid_info) {
        $tmp_key = ftok(__FILE__,chr($pid));
        $tmp_shm = shmop_open($tmp_key,'c',0644,1024);
        $child_data = @unserialize(shmop_read($tmp_shm,0,1024));
        shmop_delete($tmp_shm);
        if (!empty($child_data)) {
            $datas[$child_data['pid']] = $child_data;
        }
}
    var_dump($datas);
    var_dump($process_pool);
    shmop_delete($process_pool_shm);
}

