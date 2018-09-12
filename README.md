# Pider

<a href='https://travis-ci.org/duanqiaobb/pider' > <img align="left" style="display:inline-block" src="https://travis-ci.org/duanqiaobb/pider.svg?branch=develop"></img><a>

<div align='right' style='display:inline-block'><strong>Docmentation:</strong> English <a href="https://github.com/duanqiaobb/pider/blob/develop/doc/zh_cn/Brief.md">Chinese</a></div>

# Introduction
&ensp;&ensp; Pider is an elegant,powerful,modulized,templatized spider framework.It aims to fertilize the php community and make easier for PHP deveoper to write a spider. view [document](https://github.com/duanqiaobb/pider/wiki) in details.

# Features

+ [x] Crawler (Support)
+ [x] Crawler with multi-process (Support)
+ [x] Command line interface (Not full support )
+ [ ] Template crawling(Not support)
+ [ ] Well debug interface(Not support)
+ [x] Data cleaning(Not full support)
+ [ ] Data visuliazation(Not support)
+ [x] Distribution (Support)

# Requirments

+ PHP >= 7.1 
+ pthreads (optional for multi-threads support)
+ pcntl (optional for multi-processes support)
+ xmlreader (optional for XML file processing support) 

# Installation

**Use docker**
&ensp;&ensp;&ensp;&ensp; There are a out-of-box docker environment for use. You can just `pull` and use it right away.
```shell
docker pull jhbian/pider
git clone https://github.com/duanqiaobb/pider
cd pider
composer install
```

**Install into your laptop (only linux supported currently)**
&ensp;&ensp;&ensp;&ensp; You can run `install.sh` under root directory of the project whatever you prefer to install an environment into your laptop.

+ Install `composer` at first.(Details can be pored over on [https://getcomposer.org/](https://getcomposer.org/))

+ Set up environment

```shell
  git clone https://github.com/duanqiaobb/pider.git
  chmod u+x install.sh
  ./install.sh
```


# Usage
&ensp;&ensp;&ensp;&ensp; Hereinafter, I assume that you had set pider up ,not only environment but also the framework itself.  

## Basic Spider

&ensp;&ensp;&ensp;&ensp;This spider crawles categories of product in index page of  [jd.com](http://www.jd.com)

```shell
cd pider
mkdir example
cd example && touch JdIndexCategorySpider.php
```

```php
//In file JdIndexCategorySpider.php
<?php
use Pider\Spider;
use Pider\Http\Response;
    
class JdIndexCategorySpider extends Spider {
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

```shell
../pider JdIndexCategorySpider.php
```
```
array(46) {
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
## Spider with proxy

```shell
touch JdIndexCategoryWithProxySpider.php
```

```php
use Pider\Spider;
use Pider\Http\Response;
    
class JdIndexCategoryWithProxySpider extends Spider {
    protected $domains = 'www.jd.com';
    protected $urls = [
       'www.jd.com/'];

    //Generate urls to be crawled
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
../pider Examplespider.php

```
```
array(46) {
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

# Todo

# Contribution
&ensp;&ensp;&ensp;&ensp; If you have any ideas about this project, please don't hesitate to pull a request.
