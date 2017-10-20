<?php
$shm_key = ftok(__FILE__,0);
var_dump($shmop_id = shmop_open(98654,'c',0644,1024));
shmop_write($shmop_id,'hello',0);
var_dump(shmop_read($shmop_id,0,1024));

