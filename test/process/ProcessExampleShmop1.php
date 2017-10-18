<?php
$shm_key = ftok(__FILE__,'t');
$shm_id = shmop_open($shm_key,'c',0644,100);
var_dump($shm_id);

