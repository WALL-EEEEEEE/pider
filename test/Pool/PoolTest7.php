<?php
class Config extends Threaded { //shared global object
    protected $val = 0, $val2 = 0; 
    public function inc(){ ++$this->val; } //protected synchronizes by-object
    public function inc2() { ++$this->val2; } // no synchronization
}


class WorkerClass extends Worker {
    protected static $worker_id_next = -1;
    protected $worker_id;
    protected $config;
    public function __construct($config) {
        $this->worker_id = ++static::$worker_id_next; //static members are not available in thread but are in 'mainthread'
        $this->config  = $config;
    }

    public function run(){
        global $config;
        $config = $this->config;
        global $worker_id;
        $worker_id = $this->worker_id;
        echo "working context {$worker_id} is created!\n";
        //$this->say_config(); //globally synchronized function
        
    }

    //'protected' is synchronized by-object so WON'T work between multiple instances $config object as synchronization source
    protected function say_config() {

        global $config; //you can use the shared $config object as synchronization source.

        $config->synchronized(function() use (&$config){
            //NOTE: you can use Closures here, but if you attach a Closure to a Threaded object it will be destroyed as can't be serialized 
            var_dump($config);

        });
    }
}

class Task extends Thread{

    //Stackable still exists,it's just somehow dissappeared from docs(probally by mistake).See older version's docs for more details.
    protected $set;
    public function __construct($set) {
        $this->set = $set;
    }
    public function run() {
        global $worker_id;
        echo "task is running in {$worker_id}!\n";
        usleep(mt_rand(1,100)*100);
        $config = $this->getConfig();
        $val = $config->arr->shift();
        $config->arr[] = $this->set;
        for($i=0; $i<1000; ++$i){
            $config->inc();
            $config->inc2();
        }
    }

    public function getConfig(){
        global $config; //WorkerClass set this on thread's scope,can be reused by Tasks for additional async data source.(ie: connection pool or taskqueue to demultiplexer)
        return $config;
    }
}
$config = new Config;
$config->arr = new \Threaded();
$config->arr->merge(array(1,2,3,4,5,6));

class PoolClass extends Pool{
    public function worker_list() {
        if ($this->workers != null) 
            return array_keys($this->workers);
        return null;
    }

}
$pool = new PoolClass(3,'WorkerClass',[$config]);
$pool->worker_list();
//$pool->submitTo(0,new Task(-10)) //submitTo DOES NOT try to create worker
$spammed_id = -1;

for($i = 1; $i <= 100; ++$i) { //add some jobs
    if ($spammed_id == -1 && ($x = $pool->worker_list()) != null &&@$x[2]) {
        $spammed_id = $x[2];
        echo "spammed worker {$spammed_id} with lots of tasks from now on\n";
    }
    if ($spammed_id != -1 && ($i % 5) == 0) //every 5th job is routed to one worker,so it has 20% of the total jobs(with 3 workers it should do -33%, not it has(30+20)%, so only delegate to worker if you plan to do balancing as well...)
        $pool->submitTo($spammed_id, new Task(10*$i));
    else 
        $pool->submit(new Task(10*$i));
}
$pool->shutdown();
var_dump($config); // "var" is exactly 100000, 'val2' is probably a bit less

//also: if you disable the spammer, you'll that the order of the 'arr' is random.
