<?php
namespace Module\Data\DI;

abstract class DataInject {
    protected $data;
    
    final public function __construct() {
        $this->data =  $this->init();
        $this->persist();
    }
    public abstract function init(); 
    public abstract function update();
    public abstract function persist(); 
    public abstract function load():array; 
    final public function __invoke():array {
        return $this->load();
    }
}

