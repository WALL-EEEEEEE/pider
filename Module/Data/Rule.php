<?php

class Rule {
    private $restrict;
    private $data;
    public function __construct($data, Callable $restrict) {
        $this->restrict = $restrict;
        $this->data = $data;
    }

    public function __invoke() {
        $restrict_callback = $this->restrict;
        return $restrict_callback($this->data); 
    }
}
