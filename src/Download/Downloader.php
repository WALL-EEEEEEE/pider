<?php
namespace Pider\Download;

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

    private $response;

    public function __invoke() {
        return $this;
    }

    public function download(Stream $stream) {
        //Extract the request infomation from stream
        $request = $stream->body();
        $response = $request->request('GET');
        $response->setOrgUrl($request->getUri());
        $response->setUrl($request->getOrgUri());
        //Construct the response stream
        $response_stream = new MetaStream('RESPONSE',$response);
        return $response_stream;
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
        $this->response = $downloader->download($stream);
    }

    public function toStream() {
        return $this->response;
    }
}

