<?php

function ping($host,$timeout = 1) {
    //ICMP ping packet with a pre-caculated checksum 
    $package  = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
    $socket = socket_create(AF_INET, SOCK_RAW,1);
    socket_connect($socket, $host, null);
    $ts = microtime(true);
    socket_send($socket,$package, strlen($package),0);
    if (socket_read($socket,255)) {
        $result = microtime(true) - $ts;
    } else {
        $result = false;
    }
    socket_close($socket);
    return $result;
}

for ($i = 0, $limit = 10; $i < $limit; $i++) {
    echo round(ping('119.29.111.92')*1000,0).'ms'.PHP_EOL;
}
