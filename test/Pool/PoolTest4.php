<?php
$pool = new Pool(4);

for($i = 0; $i < 15; ++$i) {
    $pool->submit(new class extends Threaded{
        public function run() {
            sleep(2);
        }
    });
}

while($pool->collect(function($task){
    var_dump($task);
}));
echo "It has collected";
$pool->shutdown();
