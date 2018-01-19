<?php
namespace Pider;

use Pider\Template\TemplateEngine as Template;
use Pider\Http\Response;
use Pider\Http\Request;
use Pider\Kernel\WithKernel;
use Pider\Kernel\MetaStream;
use Pider\Kernel\Stream;
use Pider\Kernel\WithStream;
use Pider\Kernel\Kernel;

/**
 * @class Pider\Spider
 * Spider class is a frontend class for programmer to customize their spider. 
 */
abstract class Spider extends WithKernel {

    use Template;
    protected $urls;
    protected $domains;
    private $request;
    private $responses;

    public function __construct() {
    }

    final public function go() {

        $requests  = $this->start_requests();
        if (!is_array($requests)) {
            $requests = [$requests];
        }
        //init kernel
        $this->kernelize($requests);
    }

    /**
     * @method parse() Parse information from response of requests
     * @param  Response $response the response of requests 
     * @return array | urls | Requests
     */
    public abstract function parse(Response $response); 

    /**
     *@method start_requests()
     */
    public function start_requests():array {
        $start_requests = [];
        if (!isset($this->urls) || empty($this->urls)) {
            return [];
        }
        if (is_string($this->urls)) {
            $this->urls = [$this->urls];
        }

        foreach($this->urls as $url) {
            $start_requests[] = new Request(['base_uri'=> $url]);
        }
        return $start_requests;
    }

    /**
     * @method export() Export data parsed in different ways.
     */
    public function export(Item $items) {
    }

    public function fromStream(Stream $stream, WithStream $kernel) {
        $response = $stream->body();
        $this->parse($response);
    }

    public function toStream() {
    }

    public function isStream(Stream $stream) {
        $type = $stream->type();
        return parent::isStream($stream) && ($type == "RESPONSE");
    }

    public function kernelize($requests) {
        if(empty(self::$kernel)) {
            self::$kernel = new Kernel();
        }
        $kernel = self::$kernel;
        $if_exist = $kernel->Spider;
        if (empty($if_exist)) {
            $kernel->Spider = $this;
        }
        foreach($requests as $request) {
            $kernel->fromStream(new MetaStream("REQUEST",$request),$this);
        }
        $kernel->toStream();
    }
}
