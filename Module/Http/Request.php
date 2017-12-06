<?php
namespace Module\Http;

use GuzzleHttp\Psr7\Request as BaseRequest;
use GuzzleHttp\Client  as Client;

class Request {
    private static $proxy_callback = '';
    private $proxy = '';
    private $client = '';
    public function __construct(array $config = []) {
        $this->client = new Client($config);
    }

    public static function proxy_handler(callable $proxy_callback) {
        self::$proxy_callback = $proxy_callback;
    }

    public function proxy(string $proxy) {
        $this->$proxy = $proxy;
    }

    public function request($method, $uri = '', array $options = []) {
        if(!empty(self::$proxy_callback)) {
            $proxy_callback = self::$proxy_callback;
            $options['proxy'] = $proxy_callback(); 
        }
        if (!empty($this->proxy)) {
            $options['proxy'] = $this->proxy;
        }
        return new Response($this->client->request($method,$uri, $options));
    }

    public function __call($method,$args) {
        $this->client->__call($method,$args);
    }
}
