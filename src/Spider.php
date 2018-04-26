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
use Pider\Config;
use Pider\Support\Traits\SpiderTwigTrait as SpiderTwigTrait;
use Pider\Support\Traits\SpiderProcessTrait as SpiderProcessTrait;

/**
 * @class Pider\Spider
 * Spider class is a frontend class for programmer to customize their spider. 
 */
abstract class Spider extends WithKernel {
    use Template;
    use SpiderProcessTrait;
    use SpiderTwigTrait;
    protected $start_urls;
    protected $domains;
    protected $request;
    protected $responses;
    protected $name;
    protected static $Configs;

    public final function __construct() {
        //init kernel
        $this->kernelize();
    }

    final public function go() {
        $requests  = $this->start_requests();
        if (!is_array($requests)) {
            $requests = [$requests];
        }
        foreach($requests as &$request) {
            if(is_string($request)) {
                $request = new Request(['base_uri'=> $request]);
            }
        }
        $this->twigs($requests);
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
        if (!isset($this->start_urls) || empty($this->start_urls)) {
            return [];
        }
        if (is_string($this->start_urls)) {
            $this->urls = [$this->start_urls];
        }

        foreach($this->start_urls as $url) {
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
        $callbacks = $response->callback;
        if (empty($callbacks)) {
            $response = $this->parse($response);
        } else if (is_array($callbacks) && ! is_callable($callbacks)) {
            foreach($callbacks as $callback) {
                if (is_callable($callback)) {
                    $response = $callback($response);
                } else {
                    throw new ErrorException('Invalid callbacks passed in request');
                }
            }
        } else if (is_callable($callbacks)){
               $response = $callbacks($response);
        }
        //if $response is a new request or new request string, render it as a new request stream.
        if (is_array($response)) {
            foreach($response as $per_response) {
                if ($per_response instanceof Request ) {
                    $this->transferRequest($per_response);
                }
            }
        } else if ( $response instanceof Request ) {
                    $this->transferRequest($response);
        }
   }

    public function toStream() {
        return new MetaStream('FINISHED','');
    }

    public function isStream(Stream $stream) {
        $type = $stream->type();
        return parent::isStream($stream) && ($type == "RESPONSE");
    }

    public function kernelize() {
        if(empty(self::$kernel) || $this->processes > 1) {
            self::$kernel = new Kernel();
        }
        $kernel = self::$kernel;
        $if_exist = $kernel->Spider;
        if (empty($if_exist)) {
            $kernel->Spider = $this;
        }
        //init configs for spider
        self::$Configs = Config::copy($kernel->Configs);
        self::$Configs->setAsGlobal();
    }

    public function emitStreams($requests) {
        $kernel = self::$kernel;
        foreach($requests as $request) {
            $kernel->fromStream(new MetaStream("REQUEST",$request),$this);
        }
        $kernel->toStream();
    }

    public function transferRequest($request) {
        self::$kernel->fromStream(new MetaStream('REQUEST',$request), $this);
    }
}
