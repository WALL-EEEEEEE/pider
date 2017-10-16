<?php
$serv = new swoole_server("127.0.0.1",9501);
//set process numbers
$serv->set(array("task_worker_num"=>4));
$serv->on('receive',function($serv,$fd,$from_id,$data){
    //render the asynchronise task
    $task_id = $serv->task($data);
    echo "Dispath AsyncTask: id=$task_id\n";
});
//handle asynchronize task
$serv->on('task',function($serv,$task_id,$from_id,$data){
    echo "New AsyncTask[id=$task_id]".PHP_EOL;
    //return the result of task
    $serv->finish("$data -> OK");
});
//handle the result of task
$serv->on('finish',function($serv,$task_id,$data){
    echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
});
$serv->start();
