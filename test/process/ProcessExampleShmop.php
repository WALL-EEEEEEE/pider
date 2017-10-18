<?php
//The task number
$task = 10;
$key = ftok(__FILE__,chr(0));
$sharemem_id = shmop_open($key,"c",0644,1000);
$data = array(
    "Hello",
    "I",
    "Am",
    "Here"
);
echo "Write data\n";
echo "data:\n";
var_dump($data);
shmop_write($sharemem_id,serialize($data),0);
$get_data = shmop_read($sharemem_id,0,mb_strlen(serialize($data)));
echo "Get data\n";
echo "data:";
var_dump(unserialize($get_data));





