<?php
namespace Module\Data;

abstract class Reaction {

    private $reactionThrottle;
    private $dirty_data;
    public function __construct(Throttle $throttle) {
        $this->reactionThrottle = $throttle;
    }

    public abstract function react(array $dirty_data, Pore $pore):array;

    public function __invoke($dirty_data,Pore $pore) :array{
      $throttle = $this->reactionThrottle;
      $throttle_data = $throttle($dirty_data);
      $reacted_data = $this->react($throttle_data,$pore);
      foreach($reacted_data as $p_name => $p_value) {
          $dirty_data[$p_name] = $p_value;
      }
      return $dirty_data;
    }
}
