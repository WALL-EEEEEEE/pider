<?php
require_once(__DIR__.'/../../vendor/autoload.php');
use Workerman\Worker;

$consumer = new Worker();
$consumer->onWorkerStart = function() {
    global $pull;
    $loop = Worker::getEventLoop();
    $context = new React\ZMQ\Context($loop);
    $pull = $context->getSocket(ZMQ::SOCKET_PULL);
    $pull->bind("tcp://127.0.0.1:5555");
    $pull->on("error",function($e){
        var_dump($e->getMessage());
    });
};
Worker::runAll();
