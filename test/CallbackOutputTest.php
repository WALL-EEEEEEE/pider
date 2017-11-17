<?php

function callback($callback) {
    $callback();
}

callback(function(){
    echo "test";
    echo "callback";
});
