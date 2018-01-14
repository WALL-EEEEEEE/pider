<?php
namespace Pider\Kernel;

/**
 * @class Stream 
 * Stream Interface used by communicated
 */

abstract class Stream {
    private $type;
    private $body;
    public function __construct($type, $body) {
        $this->type = $type;
        $this->body = $body;

    }
    public function type() {
        return $this->type;
    } 
    public function body()  {
        return $this->body;
    }
    public abstract function source();
    public abstract function target();
}

