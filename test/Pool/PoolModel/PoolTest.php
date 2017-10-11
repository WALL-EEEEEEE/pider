<?php
namespace  PoolModel;
include_once("Pool.php");
include_once("Worker.php");
include_once("TestWorker.php");
use PHPUnit\Framework\TestCase;

class PoolTest extends TestCase {
    public function  testPool() 
    {
        $pool = new Pool('PoolModel\TestWorker');
        $worker = $pool->get();
        $this->assertEquals(1,$worker->id);
        $worker->id = 5;
        $pool->dispose($worker);
        $this->assertEquals(5,$pool->get()->id);
        $this->assertEquals(1,$pool->get()->id);
    }
}
