<?php
namespace Pider\Kernel;

interface WithStream {
    public function fromStream(Stream $stream);
    public function toStream();
}
