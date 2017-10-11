<?php
namespace Util;

/**
 * @class Savenger 
 * Collect some failure jobs just do  what savengers do
 */
class Savenger {
    
    private $garbage_factory = [];
    private $obj;
    private $strict_type;
    private $garbage_type;

    public function __construct(Collectable $obj) {
        $this->obj = $obj;
    }

    /**
     * @method collect
     * collect some collectable class or other type of datas
     */
    public function collect(Callable $callable,$args = []) {
       $return = call_user_func($callable,$args);
       if (is_bool($return)) {
           $return = $this->obj;
       }
       if ($this->classify($return)) {
           array_push($this->garbage_factory,$obj);
       } else {
           throw new \CollectInConsistentException("Data type is inconsisitence");
       }
    }
    /**
     *@method strict
     * specify the type of data allowed to be collected
     */
    public function strict($type) {
        $this->strict_type = $type;
    }
    private function classify($garbage) {
        $this->garbage_type = isset($this->strict_type) && !isset($this->garbage_type) ? $this->scrict_type:null;
        if (!isset($this->garbage_type)){
            $this->garbage_type =  gettype($garbage);
        }
        if ($garbage instanceof $this->garbage_type) {
            return true;
        }
        return false;
    }
}

