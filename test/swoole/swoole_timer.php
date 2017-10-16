<?php
//trigger each 2000ms
swoole_timer_tick(2000,function($timer_id){
    echo "tick-2000ms\n";
});
//trigger each 3000ms
swoole_timer_after(3000,function(){
    echo "after 3000ms.\n";
});
