<?php
include_once('../app.php');
use Module\Pider;
use GuzzleHttp\Psr7\Response;

class Taobao_tags_full extends Pider {
    protected $domains = 'www.taobao.com';

    public function parse(Response $response) {
        $html = (string)$response->getBody();
        var_dump($html);
    }
}

