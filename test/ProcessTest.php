<?php
include_once('../app.php');

use Module\Pider;
use Module\Process\Processd;
use Module\Process\Process;
use Module\Process\QueueShared;
use Module\Process\Shared;

class ProcessTest extends Pider {

    public function test() {
        $shared_data = array();
        $data = ['test1','test2'];
        $sharedData = new QueueShared($data);
        $process = new Processd();
        $process->share($sharedData);
        $process->add(function(Process $process){
            echo "process 1 ...\n";
            $data = $process->shared();
            $data[] = "process 1 ...";
            $feedbackData = new Shared($data);
            $process->feedback($feedbackData);
 
       });
        $process->add(function(Process $process){
            echo "process 2 ...\n";
            $data = $process->shared();
            $data[] = "process 2 ...";
            $feedbackData = new Shared($data);
            $process->feedback($feedbackData);
        });
        $process->add(function(Process $process){
            echo "process 3 ...\n";
            $data = $process->shared();
            $data[] = "process 3 ...";
            $feedbackData = new Shared($data);
            $process->feedback($feedbackData);

        });
        $process->add(new Process(function(Process $process) {
            echo "process in Process 1\n";
            $data = $process->shared();
            $data[] = "process in Process 1\n";
            $feedbackData = new Shared($data);
            $process->feedback($feedbackData);
        }));
        $process->add(new Process(function(Process $process){
            echo "process in Process 2\n";
            $data = $process->shared();
            $data[] = "process in Process 2\n";
            $feedbackData = new Shared($data);
            $process->feedback($feedbackData);
 
        }));
        $process->add(new Process(function(Process $process){
            echo "process in Process 3\n";
            $data = $process->shared();
            $data[] = "process in Process 3\n";
            $feedbackData = new Shared($data);
            $process->feedback($feedbackData);
        }));
        //Communicate between parent process and child process
        //$result = $process->read();
        //$process->write($result);
        $process->run();
        var_dump("Feedbacks:");
        var_dump($process->feedbacks());
    }

    public function go() {
        $this->test();
    }
} 
