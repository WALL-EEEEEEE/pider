<?php
include_once('../../app.php');

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;

$stack = new HandlerStack();
$stack->setHandler(new CurlHandler());
$stack->push(Middleware::mapResponse(function(ResponseInterface $response) {
    return $response->withHeader('X-Foo','bar');
}));
$client = new Client(['handler'=>$stack]);
$response = $client->request('GET','http://www.bing.com');
var_dump($response->getHeaders());

