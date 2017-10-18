<?php
$task = 10;
$child_processes= array();

for($i = 0; $i < $task; ++$i) {
    if (($pid=pcntl_fork()) == 0) {
        sleep(30);
        exit(0);
    }
}
$status;
for($i = 0; $i < $task; ++$i) {
    pcntl_wait($status);
}
