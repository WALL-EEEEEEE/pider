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
  [8] =>
  string(6) "家装"
  [9] =>
  string(6) "厨具"
  [10] =>
  string(6) "男装"
  [11] =>
  string(6) "女装"
  [12] =>
  string(6) "童装"
  [13] =>
  string(6) "内衣"
  [14] =>
  string(12) "美妆个护"
  [15] =>
  string(6) "宠物"
  [16] =>
  string(6) "女鞋"
  [17] =>
  string(6) "箱包"
  [18] =>
  string(6) "钟表"
  [19] =>
  string(6) "珠宝"
  [20] =>
  string(6) "男鞋"
  [21] =>
  string(6) "运动"
  [22] =>
  string(6) "户外"
  [23] =>
  string(6) "汽车"
  [24] =>
  string(12) "汽车用品"
  [25] =>
  string(6) "母婴"
  [26] =>
  string(12) "玩具乐器"
  [27] =>
  string(6) "食品"
  [28] =>
  string(6) "酒类"
  [29] =>
  string(6) "生鲜"
  [30] =>
  string(6) "特产"
  [31] =>
  string(12) "礼品鲜花"
  [32] =>
  string(12) "农资绿植"
  [33] =>
  string(12) "医药保健"
  [34] =>
  string(12) "计生情趣"
  [35] =>
  string(6) "图书"
  [36] =>
  string(6) "音像"
  [37] =>
  string(9) "电子书"
  [38] =>
  string(6) "机票"
  [39] =>
  string(6) "酒店"
  [40] =>
  string(6) "旅游"
  [41] =>
  string(6) "生活"
  [42] =>
  string(6) "理财"
  [43] =>
  string(6) "众筹"
  [44] =>
  string(6) "白条"
  [45] =>
  string(6) "保险"

}
```
