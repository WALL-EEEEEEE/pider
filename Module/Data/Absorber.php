<?php

class Absorber {

    private $absorberRules = [];
    private $dirty_data = '';
    public function __construct(&$dirty_data) {

        $this->absorberRules[] = new class extends Rule {
        };
    }

    public function adsorb() {
        $logic = true;
        foreach($absorberRules as $rule) {
            $logic &= $rule($this->dirty_data);
        }
       
        if ($logic) {
            unset($this->dirty_data);
        }
        return $this->dirty_data;
    }

}
