<?php
namespace Module\Process;

/**
 * @class QueueShared
 * Share queue-esque data between processes
 */
class QueueShared extends Shared {

    private $data = [];
    public function __construct($data) {
        $this->data = $data;
    }
    public function __init(){
    }
    public function pop(){
    }

    public function push(){
    }

    public function clear(){
    }
}
