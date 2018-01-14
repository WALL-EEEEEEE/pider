<?php
namespace Pider\Kernel;

/**
 * @WithKernel 
 * Stream Api used to communicate with Kernel
 */

class WithKernel implements WithStream {

    private $fromstreams = [];
    private $kernel;
    public function fromStream(Stream $stream, WithStream $kernel) {
        $this->fromstreams[] = $stream;
    }
    public function toStream() {
        if (empty($this->kernel)) {
            $kernel = new Kernel();
        } 
        foreach($this->fromstreams as $stream) {
            $kernel->fromStream($stream,$this);
        }
        return $kernel->toStream();
    }
    public function isStream(Stream $stream) {
        if ($stream instanceof MetaStream) {
            return true;
        }
        return false;
    }
}
