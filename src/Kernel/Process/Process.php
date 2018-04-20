<?php
namespace Pider\Kernel\Process;

/**
 * @class Process
 * This function is a simple wrapper of pcntl_fork for PHP multi-process supportant
 */
class Process {
    public $task;
    public $name;
    public function __construct(Callable $callback,$name = ''){
        $this->task = $callback;
        if (!empty($name)) {
            $this->name = $name;
        }
    }
    public function run() {
        $task = $this->task;
        cli_set_process_title($this->name);
        $task($this);
    }
    public function __invoke($name = '') {
        if (!empty($name) && empty($this->name)) {
            $this->name = $name;
        }
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
