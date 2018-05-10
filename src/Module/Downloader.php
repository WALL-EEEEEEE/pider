<?php
namespace Pider\Module;

/**
 * @class Downloader
 *
 * Dowanload url content from Internet
 */
use Pider\Kernel\WithKernel;
use Pider\Kernel\WithStream;
use Pider\Kernel\Stream;
use Pider\Kernel\MetaStream;

class Downloader extends WithKernel {

    private $responseStream;

    public function __invoke() {
        return $this;
    }

    public function download(Stream $stream) {
        //Extract the request infomation from stream
        $request = $stream->body();
        $response = $request->request();
        $response->setOrgUrl($request->getOrgUri());
        $response->setUrl($request->getUri());
        $response->callback = $request->callback;
        $response->attachment = $request->attachment;
        return new MetaStream('RESPONSE',$response);
   }

    public function isStream(Stream $stream) {
        return parent::isStream($stream) && $stream->type() == 'REQUEST';
    }

    public function fromStream(Stream $stream,WithStream $kernel) {
        $if_exist = $kernel->Downloader;
        if (empty($if_exist)) {
            $kernel->Downloader = new Downloader();
        } 
        $downloader = $kernel->Downloader;
        $this->responseStream  = $downloader->download($stream);
    }

    public function toStream() {
        return $this->responseStream;
    }
}

