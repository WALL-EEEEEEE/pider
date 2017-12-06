<?php
include_once('../../app.php');

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;

function add_header($header,$value)
{
    return function (callable $handler) use ($header,$value) {
        return function(RequestInterface $request,array $options) use ($handler,$header,$value){
            $request = $request->withHeader($header,$value);
            return $handler($request,$options);
        };
    };

}

$stack = new HandlerStack();
$stack->setHandler(new CurlHandler());
$stack->push(add_header('X-Foo','bar'));
$client = new Client(['handler'=>$stack]);
var_dump($client->getConfig());
