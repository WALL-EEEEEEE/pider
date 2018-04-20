<?php
namespace Pider\Kernel\Process;

/**
 * @class Processd 
 * 
 * Manage several processes as a process manager
 *
 */
use Pider\Kernel\Schedule;

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

    private $daemon;
    private $t_name;
    private $default_ct_name = "twig(child:pid)";

    public function __construct($name = 'twig(master)',$daemon = false) {
        $this->t_name = $name;
        $this->daemon = $daemon;
        cli_set_process_title($name);
    }
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
        //run under no daemonize
        if ($this->daemon == false) {
            $this->_nodaemonize();
       //run under daemonize
        } else  {
            echo "run under daemon".PHP_EOL;
            $this->_daemonize();
        }
    }

    public function _daemonize() {
        $secure_wrapper = pcntl_fork();

        if ($secure_wrapper == -1) {
            throw new \ErrorException('Error: Processd can\'t fork a new subprocess');
        } else if ($secure_wrapper) {
            exit(0);
        } else {
            $session = posix_setsid();
            var_dump($session);
            if ($session == -1) {
                throw new \ErrorException("DaemonProcessError: Can't detach from terminal");
            }
            $this->_nodaemonize();

        }
   }

    public function _nodaemonize() {
        
        while($process = $this->schedule()) {
            $pid_flag = pcntl_fork();
            switch($pid_flag) {
            case -1:
                throw new \ErrorException('Error: Processd can\'t fork a new subprocess');
                break;
            case 0:
                $default_ct_name = preg_replace('/pid/i',getmypid(),$this->default_ct_name);
                $process($default_ct_name);
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
