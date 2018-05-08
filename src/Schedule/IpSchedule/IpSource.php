<?php
namespace Pider\Schedule\IpSchedule;

abstract class IpSource  {
    public abstract function suck();
    public function __invoke() {
      return $this->suck();
    }
}
