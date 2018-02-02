<?php
namespace Pider\Prepost\Data;

class Throttle {
    private $restrict;
    public function __construct(Callable $restrict) {
        $this->restrict = $restrict;
    }
    public function __invoke($data):array {
        $restrict_callback = $this->restrict;
        return $restrict_callback($data); 
    }
}
