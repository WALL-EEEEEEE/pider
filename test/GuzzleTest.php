<?php
namespace UnitTest;

include_once("../app.php");
include_once('./ExtTestCase.php');
use  GuzzleHttp\Client;

class GuzzleTest extends ExtTestCase {

    public function testGuzzleGet() {

        $client = new Client([
            //Base URI is used with relative requests
            'base_uri'=>"http://httpbin.org",
            //You can set any number of default request options
            'timeout' => 20,
        ]);
        $response = $client->request('GET');
        var_dump($response);
    }
}
