# Introduction
&ensp;&ensp; Pider is an elegant,powerful,modulized,templatized spider framework.It aims to fertilize the php community and make easier for PHP developer to write a spider.

# Usage

## Create a spider

### example<1>

Examplespider.php

```php
<?php
include_once('../app.php');
use Module\Pider;
use GuzzleHttp\Psr7\Response;
    
class Examplespider extends Pider {
    protected $domains = 'www.jd.com';
    protected $urls = [
       'www.jd.com/'
    ]
    
    //Parse data from response of requests
    public function parse(Response $response) {
        $response = $response->outputEncode('utf-8');
        $product_names = $response->xpath("//ul[contains(@class,'J_sk_list')]/li/div/a/p/text()")->extract();
        var_dump($product_names);
    }
}  

```
Run the spider

```shell
./pider Examplespider.php

```

