- [快速使用](#org3c12aad)
  - [项目的基本结构](#orgdc5dd26)
  - [运行](#org6cd2bed)
    - [爬虫](#orgaf41aa7)
    - [数据处理](#org6cf5868)
    - [示例](#org6e0d87f)
  - [基本用法](#orgf3639d8)
  - [响应处理](#org8bf4d19)
  - [多进程](#orga7eabc7)


<a id="org3c12aad"></a>

# 快速使用


<a id="orgdc5dd26"></a>

## 项目的基本结构

```sh
//项目根目录
|-- Config  //配置文件
|-- LICENSE
|-- README.md
|-- composer.json
|-- composer.lock
|-- examples //示例
|-- install.sh //安装脚本
|-- pider -> src/bin/pider //爬虫管理命令工具
|-- piderd -> src/bin/piderd //爬虫分布式管理工具
|-- src  //源码
```


<a id="org6cd2bed"></a>

## 运行


<a id="orgaf41aa7"></a>

### 爬虫

```sh
./pider  spidername.php(你创建的爬虫实例)
```


<a id="org6cf5868"></a>

### 数据处理

```sh
./pider --digest digestname.php(你创建的数据处理实例)
```


<a id="org6e0d87f"></a>

### 示例

-   创建实例

```sh
mkdir spiders //创建爬虫文件夹，里面放置你写的爬虫实例
mkdir digests //创建数据处理文件夹，里面放置你写的数据处理实例
touch spiders/TestSpider.php
touch digests/TestDigest.php
```

-   编写实例

```php
//TestSpider.php
<?php
use Pider\Spider;
use Pider\Http\Response;
class TestSpider extends Spider {
     protected $start_urls = ['https://item.jd.com/1304924.html'];
     protected $domain     = ['www.jd.com'];

     public function parse(Response $response) {
	      echo "Hello Pider!".PHP_EOL;
    }
}
```

```php
//TestDigest.php
<?php
use Pider\Digest;
class TestDigest extends Digest {
     protected $start_urls = ['https://item.jd.com/1304924.html'];
     protected $domain     = ['www.jd.com'];

     public function process(Response $response) {
	      echo "Hello Digest!".PHP_EOL;
    }
}
```

-   运行实例

```sh
./pider spiders/TestSpider.php
./pider digests/TestDigest.php
```

> 注意:
> 
> <font color="#d14"> **定义的实例类名必须和文件名一致** </font>


<a id="orgf3639d8"></a>

## 基本用法

&ensp;&ensp;&ensp;&ensp; 爬虫中最常见的场景就是，请求一个 `html` 页面，或者返回 `json/xml` 格式的接口， 然后处理响应文本。

```php
use Pider\Spider;
use Pider\Http\Response;
class BasicSpider extends Spider {
     protected $start_urls = ['https://item.jd.com/1304924.html'];
     protected $domain     = ['www.jd.com'];

     public function parse(Response $response) {
	  //获取商品名称
	  $name = $response->xpath('//div[contains(@class,"itemInfo-wrap")]/div[contains(@class,"sku-name")]/text()')->extract(); 
	  //打印商品名称
	  var_dump($name);
     }
}
```

> Note: 该例子中的 `parse()` 方法是用来解析响应请求， `$start_urls` 是用来设定请求的 `urls` 。你也可以通过 `start_requests()` 来设定请求的 `urls` 。但是需要注意的是， `start_requests()` 和 `$start_urls` 两种方法只能单独使用。


<a id="org8bf4d19"></a>

## 响应处理

&ensp;&ensp;&ensp;&ensp; 爬虫的目的是从抓取到的 `html` 或者 `json/xml` 页面中分析到有用的信息。 `Pider` 提供了方便 的 `回调机制` 来从响应中提取有用的信息。默认的回调函数为 `parse()` (在未给指定的 `Request` 指定回调函数的情况下)。~Pider~ 也允许在 创建 `Request` 对象的时候，自定义回调函数。如下：

```php
use Pider\Spider;
use Pider\Http\Response;
use Pider\Http\Request;

class CustomizedCallbackSpider extends Spider {

     protected $start_urls = ['https://item.jd.com/1304924.html'];
     protected $domain     = ['www.jd.com'];

     public function parse(Response $response) {
	  //获取商品名称
	  $name = $response->xpath('//div[contains(@class,"itemInfo-wrap")]/div[contains(@class,"sku-name")]/text()')->extract(); 
	  //打印商品名称
	  var_dump($name);
	  //发起新的请求
	  return (new Request(['base_uri'=>'https://item.jd.com/1304924.html',[$this,'']]));
     }

     public function parse2(Response $response) {
	 //自定义回调
	 echo "Customized callback".PHP_EOL;
     }
}
```


<a id="orga7eabc7"></a>

## 多进程

&ensp;&ensp;&ensp;&ensp; 对于爬虫程序来说，大多数的时间都耗费在网络请求上面。当我们需要爬取大量的页面的时候，我们就不得不 考虑爬虫性能的问题， `Pider` 框架支持多进程爬虫，利用计算机的多核CPU，来提升大量爬取时候的爬虫性能。

```php
use Pider\Spider;
use Pider\Http\Response;
class MultiSpider extends Spider {
    protected $domains = [ 'www.jd.com' ];
    protected $processes = 4;
    protected $start_urls = [
	'https://item.jd.com/1378700118.html',
	'https://item.jd.com/302813.html',
	'https://item.jd.com/1304924.html',
	'https://item.jd.com/2286746.html'
    ];
    protected $count = 1;
    public function parse(Response $response) {
	   //获取商品名称
	  $name = $response->xpath('//div[contains(@class,"itemInfo-wrap")]/div[contains(@class,"sku-name")]/text()')->extract(); 
	  var_dump($name);
	  $this->count++;
    }
}
```
