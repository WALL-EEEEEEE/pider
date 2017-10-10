<?php

class job extends Worker{

    public $val;
    protected $complete = false;
    public function __construct($val) {
        //init some properties
        $this->val = $val;
    }

    public function run() {
        //do some work
        sleep(3);
        $this->val = $this->val.file_get_contents('http://www.example.com/',null,null,3,20);
        $this->complete = true;
    }
    public function isComplete(){
        return $this->complete;
    }
}

// At most 3 threads will work at once
$p = new Pool(4,\job::class,array('0','1','2','3'));

//Add tasks to pool queue
for($i = 0; $i < 4; $i++ ){
   $p->submit(new job($i));
}

//garbage collection check / read results
$p->collect(function($mytask){
    echo "Collecting....";
    echo $mytask->val;
});
