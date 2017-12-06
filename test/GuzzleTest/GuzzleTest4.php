<?php
include_once('../../app.php');

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;

function add_response_header($header,$value)
{
    return function(callable $handler) use ($header,$value) {
        return function(RequestInterface $request, array $options) use ($handler, $header,$value) {
               $promise = $handler($request,$options);
               return $promise->then(function(ResponseInterface $response) use ($header,$value){
                   return $response->withHeader($header,$value);
               });
        }; 
    };
}

$stack = new HandlerStack();
$stack->setHandler(new CurlHandler());
$stack->push(add_response_header('X-Foo','bar'));
$client = new Client(['handler'=>$stack]);
$response = $client->request('GET','www.bing.com');
var_dump($response->getHeaders());
