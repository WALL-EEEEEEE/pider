# Introduction
&ensp;&ensp; Pider is an elegant,powerful,modulized,templatized spider framework.It aims to fertilize the php community and make easier for PHP developer to write a spider.

# Features

+ Crawler 
+ Crawler with multi-process
+ Command line interface
+ Template crawling
+ Well debug interface
+ Data cleaning
+ Data visuliazation

# Requirments

+ PHP >= 7.1 with `pthreads` , `pcntl` and `xmlreader` extensions enabled

# Installation

```shell
  git clone https://github.com/duanqiaobb/pider.git
  chmod u+x install.sh
  ./install.sh
```

# Usage

## Create a simple spider

Examplespider.php

```php
<?php
use Pider\Spider;
use Pider\Http\Response;
    
class Examplespider extends Spider {
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
## Create a spider with proxy

```php
use Pider\Spider;
use Pider\Http\Response;
    
class Examplespider extends Spider {
    protected $domains = 'www.jd.com';
    protected $urls = [
       'www.jd.com/'];

    public function start_requests():array {
        $std_urls = ['www.jd.com'];
        Request::proxy_handler(function(){
            return xxx(); //function return a proxy ip
        });
       return $std_urls; //url or Request object array
    }

    
    //Parse data from response of requests
    public function parse(Response $response) {
        $response = $response->outputEncode('utf-8');
        $category_names = $response->xpath("//ul[contains(@class,'cate_menu')]/li/a/text()")->extract();
        var_dump($category_names);
    }
} 
```

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
