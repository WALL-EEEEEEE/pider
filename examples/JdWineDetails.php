<?php
include_once('../app.php');

use Module\Pider;
use GuzzleHttp\Psr7\Response;
use Util\Api;

class JdWineDetails extends Pider {
    protected $domains = [
        'www.jd.com'
    ];

    public function start_requests(){
        //Load the url infomation from standard database
        $std_urls = [];
        $crawler_urls = [];
        $std_urls = Api::get_standard_products_url(10000,'1');
        var_dump($std_urls);
    }

    public function parse(Response $response) {
        echo "Hello!\n";
    }
}

