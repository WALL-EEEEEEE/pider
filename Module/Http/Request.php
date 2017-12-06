<?php
namespace Module\Http;

use GuzzleHttp\Psr7\Request as BaseRequest;
use GuzzleHttp\Client  as Client;

class Request {

    private $proxy;
    private static $proxy_handler;
    private $client;
    public function __construct(array $config = []) {
        $this->client = new Client($config);
    }
    public static function proxy_handler(callable $proxy_handler) {
        self::$proxy_handler = $proxy_handler;
    }
    public function proxy(string $proxy) {
        $this->proxy = $proxy;
    }
    public function request($method, $uri = '', array $options = []) {
        if (!empty(self::$proxy_handler)) {
            $proxy_handler = self::$proxy_handler;
            $options['proxy'] =  $proxy_handler();
        }
        if (!empty($proxy)) {
            $options['proxy'] = $this->proxy;
        }
        var_dump($options);
        return new Response($this->client->request($method,$uri, $options));
    }

    public function __call($method,$args) {
        $this->client->__call($method,$args);
    }
}
