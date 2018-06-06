<?php
namespace Pider\Kernel\Event;

class Event {
    private $etype ;
    public function __construct($type) {
        $this->etype = $type;
    }
    public function type() {
        return $this->etype;
    }
}


