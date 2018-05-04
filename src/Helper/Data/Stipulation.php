<?php
namespace Util\Data;

class Stipulation {
    private $rules = [];
    private $datas = [];
    public function __construct(array $datas) {
        if(empty($data)) {
            throw new ErrorException("Data to be stipulated can't be none!");
        }
        $this->datas = $data;
    }
    public function dispose(Rule $rule) {
        if (!in_array($rule,$this->rules)) {
            $rules[] = $rule;
        }
    }

    public function execute() {
        foreach($rules as $rule) {
            //detect if the  rule matched
            if ($rule->detect()) {
                $this->datas = $rule->execute($this->datas);
            }
        }
        return $this->datas;
    }
}

