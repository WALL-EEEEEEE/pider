<?php
class Something {

}
class MyWorker extends Worker {
    public function __construct(Something $something){
        $this->something = $something;
    }

    public function run() {
        /** ... **/
    }
}

$pool = new Pool(8,\MyWorker::class,[new Something()]);
var_dump($pool);

