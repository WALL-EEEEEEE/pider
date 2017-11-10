<?php
namespace Module\Process;;

/**
 * @class Process
 * This function is a simple wrapper of pcntl_fork for PHP multi-process supportant
 */
class Process {
    private $task;
    public function __construct(callback $callback){
        $this->task = $callback;
    }
    public function run() {
        $this->task();
    }
}
