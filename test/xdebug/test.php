<?php

class test {
    public $pub =false;
    private $priv = true;
    protected $prot = 42;
}

$t = new test;
$t->pub = $t;
$data = array(
    'one' => 'a somewhat long string!',
    'two' => array(
        'two.one' =>array(
            'two.one.zero' => 210,
            'two.one.one'=> array(
                'two.one.one.zero' => 3.141592564,
                'two.one.one.one'=>2.7,
            ),
        ),
    ),
    'three'=>$t,
    'four'=>range(0,5),
);
var_dump($data);

