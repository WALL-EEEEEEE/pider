<?php

return $config = [
    'Request.Option'=>[
        'proxy'=> false,
        'user-agent'=>'Pider',
    ],
    "Proxy"=> "ProxyTest",
    "Database"=> [
        //Define connections 
        'crawler'=>[
            'driver'    => 'mysql',
            'host'  => '119.29.98.158:3306',
            'database'  => 'crawler',
            'username'  => 'spider',
            'password'  => '9kachaspider',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
        'phpspider' => [
            'driver'    => 'mysql',
            'host'  => '119.29.98.158:3306',
            'database'  => 'phpspider',
            'username'  => 'spider',
            'password'  => '9kachaspider',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
    ]
];
