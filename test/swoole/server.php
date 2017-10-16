<?php
//Create server object,listening to port 127.0.01:9501
$serv = new swoole_server("127.0.0.1",9501);

//register connect event
$serv->on('connect',function($serv,$fd){
    echo "Client: Connect.\n";
});

//register receive event
$serv->on('receive',function($serv,$fd,$from_id,$data){
    $serv->send($fd,"Server:".$data);
});

//listen close event
$serv->on('close',function($serv,$fd){
    echo "Client: Close.\n";
});
//start serve
$serv->start();

