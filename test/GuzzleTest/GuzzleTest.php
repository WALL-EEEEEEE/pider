<?php
namespace UnitTest;

include_once('../ExtTestCase.php');
use  GuzzleHttp\Client;
use  GuzzleHttp\Psr7;
use  GuzzleHttp\Psr7\Http\Message\ResponseInterface;
use  GuzzleHttp\Exception\RequestException;
use  GuzzleHttp\Promise;
use  GuzzleHttp\Pool;

class GuzzleTest extends ExtTestCase {

    public function testGuzzleGet() {

        $client = new Client([
            //Base URI is used with relative requests
            'base_uri'=>"http://httpbin.org",
            //You can set any number of default request options
            'timeout' => 20,
        ]);
        $response = $client->request('GET');
    }

    public function testGuzzleMagicMethods(){
        $client = new Client();
        $response = $client->get('http://httpbin.org/get');
        var_dump((string) $response->getBody());
        $response = $client->delete('http://httpbin.org/delete');
        var_dump((string) $response->getBody());
        $response = $client->head('http://httpbin.org/get');
        var_dump((string) $response->getBody());
        $response = $client->options('http://httpbin.org/patch');
        var_dump((string) $response->getBody());
        $response = $client->post('http://httpbin.org/post');
        var_dump((string) $response->getBody());
        $response = $client->put('http://httpbin.org/put');
        var_dump((string) $response->getBody());
    }

    public function testGuzzleAsyncMagicMethods(){
        $client = new Client();
        $promise = $client->getAsync('http://httpbin.org/get');
        echo $promise."\n";
        $promise = $client->deleteAsync('http://httpbin.org/delete');
        echo $promise."\n";
        $promise = $client->headAsync('http://httpbin.org/get');
        echo $promise."\n";
        $promise = $client->optionAsync('http://httpbin.org/get');
        echo $promise."\n";
        $promise = $client->patchAsync('http://httpbin.org/patch');
        echo $promise."\n";
        $promise = $client->postAsync('http://httpbin.org/post');
        echo $promise."\n";
        $promise = $client->putAsync('http://httpbin.org/put');
        echo $promise."\n";
    }

    public function testGuzzleAsyncRequestMethods(){
        $client = new Client();
        $headers = ['X-Foo'=>'Bar'];
        $body = 'Hello!';
        $request = new Request('HEAD','http://httpbin.org/head',$headers,$body);
        var_dump($request);
        //Or, if you don't need to pass in a request instance
        $promise = $client->requestAsync('GET','http://httpbin.org/get');
        var_dump($promise);
    }

    public function testGuzzlePromiseMethod() {

        $client = new Client();
        $promise = $client->requestAsync('GET','http://httpbin.org/get');
        $promise->then(
            function (ResponseInterface $res) {
                echo $res->getStatusCode()."\n";
            },
            function(RequestException $e) {
                echo $e->getMessage()."\n";
                echo $e->getRequest()->getMethod();
            }
        );
    }

    public function testGuzzleConcurrentMethod() {
        $client = new Client(['base_uri' => 'http://httpbin.org/']);
        //Initiate each request but do not block
        $promises = [
            'image' => $client->getAsync('/image'),
            'png'   => $client->getAsync('/image/png'),
            'jpeg'  => $client->getAsync('/image/jpeg'),
            'webp'  => $client->getAsync('/image/webp')
        ];
        //Wait on all of the requests to complete.Throws a ConnectException
        //if any of the requets fail
        $results = Promise\unwrap($promises);
        //Wait for the requests to complete,even if some of them fail
        $results = Promise\settle($promises)->wait();
        //You can access each result using the key provideed to the unwrap function
        echo $results['image']['value']->getHeader('Content-Length')[0];
        echo $results['png']['value']->getHeader('Content-Length')[0];
    }

    public function testGuzzlePoolMethod(){
        $client = new Client();
        $requests = function($total) {
            $uri = 'http://127.0.0.1:8126/guzzle-server/perf';
            for ($i = 0; $i < $total; $i++) {
                yield new Request('GET',$uri);
            }
        };
        $pool = new Pool($client,$requests(100),[
            'concurrency'=>5,
            'fullfilled' => function($response, $index) {
                //this is delivered each successful response
            },
            'rejected' => function($reason, $index) {
                //this is delivered each failed request
            },
        ]);
        //Initiate the transfers and create a promise
        $promise = $pool->promise();
        //Force the pool of requests to complete.
        $promise->wait();
    }

    public function testGuzzlePoolAsyncMethod() {
        $client = new Client();
        $requests = function($total) use ($client) {
            $uri = 'http://127.0.0.1:8126/guzzle-server/perf';
            for ($i = 0; $i < $total ; $i++) {
                yield function() use ($client, $uri) {
                    return $client->getAsync($uri);
                };
            }
        };
        $pool = new Pool($client,$requests(100));
    }

    public function testGuzzleResponseMethod() {
        $client = new Client();
        $response = $client->get('www.taobao.com');
        $code = $response->getStatusCode();
        var_dump($code);
        $reason = $response->getReasonPhrase();
        var_dump($reason);
        //Check if a header exists.
        if ($response->hasHeader('Content-Length')) {
            echo "It exists\n";
        }
        //Get a header from the response.
        var_dump($response->getHeader('Content-Length'));
        //Get all of the response headers.
        foreach($response->getHeaders() as $name => $value ) {
            echo "$name".':'.implode(',',$value)."\r\n";
        }
    }

    public function testGuzzleGetbodyMethod(){
        $client = new Client();
        $response = $client->get('www.taobao.com');
        $body = $response->getBody();
        //Implicity cast the body to a string and echo it
        //echo $body;
        //Explicity cast the body to a string
        $stringBody = (string) $body;
        //var_dump($stringBody);
        //Read 10 bytes from the boxy
        $tenBytes = $body->read(10);
        var_dump($tenBytes);
        //Read the remaining contents of the body as a string
        $remainingBytes = $body->getContents();
        var_dump($remainingBytes);
    }

    public function testGuzzleStreamMethod() {
        $client = new Client();
        //Provide the body as a string.
        $r = $client->request('POST','http://httpbin.org/post',['body'=>'raw data']);
        var_dump((string)$r->getBody());
        //Provide an fopen resource.
        $body = fopen('./test.data','r');
        $r = $client->request('POST','http://httpbin.org/post',['body'=>$body]);
        var_dump((string) $r->getBody());
        //Use the stream_for() function to create a PSR-7 stream
        $body = \GuzzleHttp\Psr7\stream_for('hello!');
        $r = $client->request('POST','http://httpbin.org/post',['body'=> $body]);
        var_dump((string) $r->getBody());
        $r = $client->request('PUT','http://httpbin.org/put',['json'=>['foo'=>'bar']]);
        var_dump((string) $r->getBody());
    }

    public function testGuzzleCookieMethod() {
        $client = new Client();
        //Use a specific cookie jar
        $jar = new \GuzzleHttp\Cookie\CookieJar;
        $r = $client->request('GET','http://httpbin.org/cookies',[
            'cookies'=> $jar
        ]);
        var_dump((string) $r->getBody());
    }

    public function testGuzzleRequestExceptionMethod() {
        $client = new Client();
        try {
            $client->request('GET','https://github.com/_abc_123_404');
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

    }
}
