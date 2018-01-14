<?php
namespace Pider\Kernel;


interface WithStream {
    public function isStream(Stream $stream); 
    public function fromStream(Stream $stream, WithStream $fromObject);
    public function toStream();
}
