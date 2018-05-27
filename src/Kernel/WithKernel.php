<?php
namespace Pider\Kernel;

/**
 * @WithKernel 
 * Stream Api used to communicate with Kernel
 */

abstract class WithKernel implements WithStream {
    protected  static $kernel;
    public abstract function fromStream(Stream $stream, WithStream $kernel );
    public abstract function toStream(); 

    public function isStream(Stream $stream) {
        if ($stream instanceof MetaStream) {
            return true;
        }
        return false;
    }
}
