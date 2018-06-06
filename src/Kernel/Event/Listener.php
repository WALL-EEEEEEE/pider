<?php
namespace Pider\Kernel\Event;

trait Listener {
    private $listeners = [];

    public function on(string $event, Callable  $callable) {
        $this->listeners[$event] = $callable;
    }
}

