<?php
namespace Module\Data;

class Rule {
    private $restrict;
    public function __construct(Callable $restrict) {
        $this->restrict = $restrict;
    }
    public function __invoke($data):bool {
        $restrict_callback = $this->restrict;
        return $restrict_callback($data); 
    }
}
