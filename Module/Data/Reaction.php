<?php
class Reaction {

    private $reactionRules = [];
    private $dirty_data = '';
    public function __construct(&$dirty_data) {

        $this->reactionRules[] = new class extends Rule {
        };
    }

    public function react() {
        $logic = true;
        foreach($reactionRulesa as $rule) {
            $logic &= $rule($this->dirty_data);
        }
       
        if ($logic) {
            unset($this->dirty_data);
        }
        return $this->dirty_data;
    }



}
