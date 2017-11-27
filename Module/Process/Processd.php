<?php
namespace Module\Process;

/**
 * @class Processd 
 * 
 * Manage several processes as a process manager
 *
 */
use Module\Schedule\Schedule;

class Processd  implements Schedule{
    /**
     * @member  $shedule_pool 
     * Hold all the processes to be scheduled
     */
    private $schedule_pool= [];

    /**
     * @member $process_pool
     * Hold all the processes being sheduled
     */
    private $process_pool = [];

    private $feedbacks = [];
    /**
     *@method add(callback | Process $process )  attach a process to manager
     *@param $process mix $process can be only a callback or a Process object
     *@return void
     */
    public function add($process) {
        if (!is_callable($process)) {
            throw new \InvalidArgumentException("Error:  Arugment #1 must be a Process Object");
        }
        if (! $process instanceof  Process) {
            $process = new Process($process);
        }
        $this->schedule_pool[] = $process;
    }
    /**
     * @method shared(Shared $shared) shared data that is accessible for both parent process and child process.
     *
     * @param $shared Shared  Data shared
     */
    public function share(Shared $shared) {
        $pid = getmypid();
        $shared->store($pid);
    }

    /**
     * @method __feedbacks() collected all feedback from child process agaist the parent process' shared data
     */
   private function __feedbacks() {
        $pid_pool = array_keys($this->process_pool);
        $feeds_data = [];
        foreach ($pid_pool  as $chid) {
            $feeds = Shared::restore($chid);
            Shared::destory($chid);
            $feeds_data = array_merge($feeds_data,$feeds);
        }
        $this->feedbacks = $feeds_data;
    }

    public function feedbacks():array {
        return $this->feedbacks;
    }
    public function schedule() {
        if (!empty($this->schedule_pool)) {
           return array_shift($this->schedule_pool);
        }
    }
    public function read(array $pid = []) {

    }
    public function write(array $data, array $pid = []) {
    }

    /**
     * @method run() start to shedule and manager process
     * @return void
     */
    public function run() {
        while($process = $this->schedule()) {
            $pid_flag = pcntl_fork();
            switch($pid_flag) {
            case -1:
                throw new \ErrorException('Error: Processd can\'t fork a new subprocess');
                break;
            case 0:
                $process();
                exit(0);
                break;
            default:
                $this->process_pool[$pid_flag] = ['task'=>$process,'status'=>1];
           }
        }

        foreach($this->process_pool as $pid => $pcallback) {
            if (($chpid = pcntl_waitpid($pid,$status)) > 0) {
                $this->process_pool[$pid_flag]['status'] =  0;
            }
        }
        $this->__feedbacks();
        Shared::destory(getmypid());
   }

}
