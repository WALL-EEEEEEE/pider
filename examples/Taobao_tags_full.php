<?php
include_once('../app.php');
use Module\Pider;

class Taobao_tags_full extends Pider {
    private $url_domain = 'www.taobao.com';
    public function parse(Response $response) {
        $json = $response->json;
        var_dump($json);
    }
}

