<?php
namespace Pider;

use Pider\Template\TemplateEngine as Template;
use Pider\Http\Response;
use Pider\Http\Request;
/**
 * @class Pider\Spider
 * Spider class is a frontend class for programmer to customize their spider. 
 */
abstract class Spider {
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
       foreach($requests as $request) {
           $response = '';
           $url = '';
           if ($request instanceof Request) {
               $this->request = $request;
               $response = $this->request->request('GET','',[
                   'on_stats' => function ($stats) use (&$url) {
                       $url = $stats->getEffectiveUri();
                   },
                   'http_errors'=>false,
                   'timeout'=>60,
                   'connect_timeout'=> 60
               ]);
               $response->setOrgUrl($this->request->getUri());
           } else {
               $httpRequest = new Request();
               $response = $httpRequest->request('GET',$request,[
                   'on_stats' => function ($stats) use (&$url) {
                       $url = $stats->getEffectiveUri();
                   },
                   'http_errors'=> false,
                   'timeout'=> 60,
                   'connect_timeout'=> 60,
               ]);
               $response->setOrgUrl($request);
           }
           $response->setUrl($url);
          if (!empty($response)) {
               $items = $this->parse($response);
               //$this->export($items);
           }
        }
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
     *
     */
    public function export(Item $items) {

    }
}

