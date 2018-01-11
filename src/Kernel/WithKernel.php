<?php
namespace Pider\Kernel;

/**
 * @WithKernel 
 * Stream Api used to communicate with Kernel
 */
class WithKernel implements WithStream {

    private $fromstreams = [];
    private $kernel;
    public function fromStream(Stream $stream) {
        $this->fromstreams[] = $stream;
    }
    public function toStream() {
        $kernel = new Kernel();
        foreach($this->fromstreams as $stream) {
            $kernel->fromStream($stream);
        }
    }
}
