# Introduction
&ensp;&ensp; Pider is an elegant,powerful,modulized,templatized spider framework.It aims to fertilize the php community and make easier for PHP developer to write a spider.

# Usage

## Create a spider

### Example1

Examplespider.php

```php
<?php
use Module\Pider;
use Module\Http\Response;
    
class Examplespider extends Pider {
    protected $domains = 'www.jd.com';
    protected $urls = [
       'www.jd.com/'];
    
    //Parse data from response of requests
    public function parse(Response $response) {
        $response = $response->outputEncode('utf-8');
        $category_names = $response->xpath("//ul[contains(@class,'cate_menu')]/li/a/text()")->extract();
        var_dump($category_names);
    }
} 

```

Run the spider:

```shell
./pider Examplespider.php

```
Scrape Result:
```
rray(46) {
  [0] =>
  string(12) "家用电器"
  [1] =>
  string(6) "手机"
  [2] =>
  string(9) "运营商"
  [3] =>
  string(6) "数码"
  [4] =>
  string(6) "电脑"
  [5] =>
  string(6) "办公"
  [6] =>
  string(6) "家居"
  [7] =>
  string(6) "家具"
  ...
}
```
