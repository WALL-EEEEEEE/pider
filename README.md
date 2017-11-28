# Introduction
&ensp;&ensp; Pider is an elegant,powerful,modulized,templatized spider framework.It aims to abundant the php community and make easier for PHP developer to write a spider.

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

    }
}  

```
Run the spider

```shell
./pider Examplespider.php

```

