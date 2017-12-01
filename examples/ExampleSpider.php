<?php

use Module\Pider;
use Module\Http\Response;
    
class Examplespider extends Pider {
    protected $domains = 'www.jd.com';
    protected $urls = [
       'www.jd.com/'
    ];
    
    //Parse data from response of requests
    public function parse(Response $response) {
        $response = $response->outputEncode('utf-8');
        $category_names = $response->xpath("//ul[contains(@class,'cate_menu')]/li/a/text()")->extract();
        var_dump($category_names);
    }
}  
