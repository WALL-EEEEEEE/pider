<?php

use Extension\Process;
use Extension\SharedData;

class ProcessTest extends Pider {

    public function test() {
        $shared_data = array();
        $sharedData = new SharedData($data);
        $process = new Process();
        $process->share($shared_data);
        $process->add(function(){
            echo "process 1 ...\n";
        });
        $process->add(function(){
            echo "process 2 ...\n";
        });
        $process->add(function(){
            echo "process 3 ...\n";
        });
        //Communicate between parent process and child process
        $result = $process->read();
        $process->write($result);
        $process->run();
    }
} 
