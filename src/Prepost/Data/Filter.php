<?php
namespace Pider\Prepost\Data;

abstract class Filter {

    private $filterThrottle;
    private $dirty_data = '';
    public function __construct(Throttle $throttle) {
        $this->filterThrottle = $throttle;
    }

    public abstract function filter(array $dirty_data, Pore $pore); 

    public function __invoke(array $dirty_data,Pore $pore) {
        $this->dirty_data = $dirty_data;
        $throttle = $this->filterThrottle;
        $dirty_data  = $throttle($dirty_data);
        return $this->filter($dirty_data,$pore);
    }
}
