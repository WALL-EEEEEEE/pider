<?php
class T extends Threaded {
    public function __construct($input) {
        $this->input = $input;
    }
    public function run() {
        $this->data = 2*3.141592*($this->input); //2*Pi*r
    }
}

class W extends Worker {
    public function run() {
    }
}

class P extends Pool {
    public $results = [];
    public function process() {
        while($this->collect(function(Collectable $job){
            $this->results[$job->input] = $job->data;
            return true;
        }));
        $this->shutdown();
        return $this->results;
    }
}
$i = 0;
$pool = new P(16,W::class);
do {
   $pool->submit(new T($i+1));
   $i++;
}while($i < 16);
$data = $pool->process();
var_dump($data);
