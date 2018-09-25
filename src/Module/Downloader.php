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
use Pider\Log\Log as Logger;

class Downloader extends WithKernel {

    private $responseStream;
    private static $logger;

    public function __invoke() {
        return $this;
    }

    public function download(Stream $stream) {
        //Extract the request infomation from stream
        self::$logger = Logger::getLogger();
        $logger = self::$logger;
        $request = $stream->body();

        if ($request->isProxied()) {
            $logger->debug('Request for '.$request->getOrgUri().' <through proxy:'.$request->getProxy().'>');
        } else {
            $logger->debug('Request for '.$request->getOrgUri());
        }
        $response = $request->request();
        $response->setOrgUrl($request->getOrgUri());
        $response->setUrl($request->getUri());
        $response->callback = $request->callback;
        $response->attachment = $request->attachment;
        $response_log = 'Request for '.$request->getOrgUri().' < '.$response->getStatusCode().', '.$response->getReasonPhrase().' >';
        $logger->debug($response_log);

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

