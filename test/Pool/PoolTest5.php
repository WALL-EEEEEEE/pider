<?php
class MyWork extends Thread {
    protected $complete;
    public function __construct() {
        $this->complete = false;
    }
    public function run() {

        sleep(3);
        printf("Hello from %s in Thread #%lu  \n",
        __CLASS__,$this->getThreadId());
        $this->complete = true;
    }

    public function isComplete() {
        return $this->complete;
    }

}

class Something {}

class MyWorker extends Worker {
    public function __construct(Something $something) {
       $this->something = $something;
    }
    public function run() {
        /** ... **/
    }
}

$pool = new Pool(8,\MyWorker::class, [new Something()]);
$pool->submit(new MyWork());
$pool->collect(function($work){
    var_dump("Collecting");
     return $work->isComplete();
});
var_dump($pool);

