<?php
namespace Module\Data;

abstract class Absorber {

    private $absorberRule;
    private $dirty_data = '';
    public function __construct(Rule $rule) {
        $this->absorberRule = $rule;
    }

    public abstract function absorb(); 

    public function __invoke($dirty_data) {
        $rule = $this->absorberRule;
        if ($rule()) {
            $this->absorb();
        }
    }
}
