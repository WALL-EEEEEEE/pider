<?php
namespace Module\Process;

/**
 * @class Processd 
 * 
 * Manage several processes as a process manager
 *
 */
use Module\Schedule;

class Processd  implements Schedule{
    /**
     * @member  $process_pool 
     * Hold all the processes
     */
    private $process_pool = [];
    /**
     *@method add(callback | Process $process )  attach a process to manager
     *@param $process mix $process can be only a callback or a Process object
     *@return void
     */
    public function add($process) {
    }
    /**
     * @method shared(Shared $shared) shared data that is accessible for both parent process and child process.
     *
     * @param $shared Shared  Data shared
     */
    public function shared(Shared $shared) {
    }

    /**
     * @method go() start to shedule and manager process
     * @return void
     */
    public function go() {
        $schedule = new Schedule($this->process_pool);
        while($process = $schedule->shedule()) {
            $process->run();
        }
    }
}
