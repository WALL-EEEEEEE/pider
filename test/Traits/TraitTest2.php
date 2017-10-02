<?php

trait HelloWorld{
    public function sayHello(){
        echo "Hello World!";
    }
}

class TheWorldsNotEnough{
    use HelloWorld;
    public function sayHello(){
        echo "Hello Universe!";
    }
}
$o = new TheWorldsNotEnough();
$o->sayHello();

?>
