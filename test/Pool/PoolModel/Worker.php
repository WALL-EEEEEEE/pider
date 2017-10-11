<?php
namespace PoolModel;
class Worker
{
    public function __construct()
    {
        //let's say that constructor does really expensive work
        // for example creates 'thread'
        
    }
    public function run($image, array $callback) 
    {
        //do something with $image
        //and when it's done,execute callback
        call_user_func($callback,$this);
    }
}


