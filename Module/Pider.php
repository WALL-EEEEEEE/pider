<?php
namespace Module;

use Module\Template\TemplateEngine as Template;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;

/**
 * @class Pider
 * Handle all spider operation 
 */
abstract class Pider {
    use Template;
    protected $urls;
    protected $domains;
    private $responses;

    public function __construct() {
    }

    final public function go() {

       $urls = $this->start_requests();
       foreach($urls as $url) {
            $httpcli = new Client();
            $response = $httpcli->request('GET',$url);
            if (!empty($response)) {
                $items = $this->parse($response);
//                $this->export($items);
            }
        }
    }

    /**
     * @method parse() Parse information from response of requests
     * @param  Response $response the response of requests 
     * @return array | url | Request
     */
    public abstract function parse(Response $response); 

    /**
     *@method start_requests()
     */
    public function start_requests() {
        $start_requests = [];
        if (!isset($this->urls) || empty($this->urls)) {
            return false;
        }
        if (is_string($this->urls)) {
            $this->urls = [$this->urls];
        }

        foreach($this->urls as $url) {
            $start_requests[] = new Request($url);
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

