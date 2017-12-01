<?php
namespace Module\Http;

use GuzzleHttp\Psr7\Request as BaseRequest;
use GuzzleHttp\Client  as Client;

class Request {

    private $client = '';
    public function __construct(array $config = []) {
        $this->client = new Client($config);
    }

    public function request($method, $uri = '', array $options = []) {
        return new Response($this->client->request($method,$uri, $options));
    }
}
