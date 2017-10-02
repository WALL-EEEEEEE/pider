<?php

class A {
    public  function  f() {
        var_dump(static::class);
        return static::class;
    }
}


class B extends A { 
}

$b = new B;
var_dump($b->f());

