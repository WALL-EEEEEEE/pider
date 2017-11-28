<?php
include_once('../app.php');

use Module\Pider;
use GuzzleHttp\Psr7\Response;

class JdWineDetails extends Pider {
    protected $domains = [
        'www.jd.com'
    ];

    public function start_requests() {
        //Load the url infomation from standard database
        $std_urls = [];
        $crawler_urls = [];
    }

    public function parse(Response $response) {
        echo "Hello!\n";
    }
}

