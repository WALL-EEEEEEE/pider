<?php
use Module\Pider;
use Module\Http\Response;
use Module\Http\Request;
use Module\Data\GrapeWine\GrapeWineActivedCarbon;
use Module\Data\Pore;
use Util\Api;
use Extension\DBExtension;


DBExtension::switch_db('jhbian_spider');
$GLOBALS['website']['id'] = 1;
class GuzzleProxy2 extends Pider {

    protected $domains = [
        'www.jd.com'
    ];

    public function start_requests():array {
        Request::proxy_handler(function() {
           return Api::getIp();
        });
        return ['http://httpbin.org/ip'];
    }

    public function parse(Response $response) {
        $response = $response->outputEncode('utf-8');
        var_dump((string)$response->getBody());
    }
}

