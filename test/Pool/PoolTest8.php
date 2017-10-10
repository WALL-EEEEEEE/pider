<?php

class My extends Thread {
    public function run() {
        /** cause this thread to wait **/
        $this->synchronized(function($thread){
            if (!$thread->done) {
                $thread->wait();
            }  
            var_dump("I am not waiting");
        },$this);
    }
}
$my = new My();
$my->start();

/** send notification to the waiting thread **/
$my->synchronized(function($thread) {
    var_dump('notify');
    $thread->done = true;
    $thread->notify();
},$my);
var_dump("hello");
