<?php
namespace Module\Process;

/**
 * @class Process
 * This function is a simple wrapper of pcntl_fork for PHP multi-process supportant
 */
class Process {
    public $task;
    public function __construct(Callable $callback){
        $this->task = $callback;
    }
    public function run() {
        $task = $this->task;
        $task($this);
    }
    public function __invoke() {
        $this->run();
    }
    public function shared() {
        $ppid = posix_getppid(); 
        $shared_data = Shared::restore($ppid);
        return $shared_data;
    }
    public function feedback(Shared $feedback) {
        $pid = getmypid();
        $feedback->store($pid);;
    }
}
