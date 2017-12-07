<?php

include_once('../../app.php');

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use Util\Api;

function add_proxy_callback($proxy_callback) {
    return function (callable $handler) use ($proxy_callback) {
        return function (RequestInterface $request,$options) use ($handler,$proxy_callback) {
            $ip = $proxy_callback();
            $options['proxy'] = $ip;
            return $handler($request,$options);
        };

    };
} 

$stack = new HandlerStack();
$stack->setHandler(new CurlHandler());
$stack->push(add_proxy_callback(function() {
    return Api::getIp();
}));
$client = new Client(['handler'=>$stack]);
$response = $client->request('GET','http://httpbin.org/ip');
var_dump((string)$response->getBody());

