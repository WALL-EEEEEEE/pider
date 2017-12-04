<?php
namespace Module\Data\DI;

interface DataInjectInterface {
    public function init();
    public function update(); 
    public function persist(); 
    public function load();
}

