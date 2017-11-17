<?php

function ping($host,$port,$timeout) {
    $tB = microtime(true);
    $fP = fsockopen($host,$port,$errno,$errstr,$timeout);
    if (!$fP) {
        return 'down';
    }
    $tA = microtime(true);
    return round((($tA - $tB) * 1000), 0)." ms";
}

//Echoing it willl display the ping if the host is up, if not it'll say 'down'.
for ($i = 0, $limit = 10; $i < $limit; $i++) {
    echo ping("119.29.111.92",22,10)."\n";
}
