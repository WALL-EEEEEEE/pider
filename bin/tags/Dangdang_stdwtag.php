<?php

/** 当当网标签分析程序
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/8/18
 * Time: 17:26
 */
require_once(dirname(__FILE__)."/../phpspider/core/init.php");
require_once(dirname(__FILE__)."/../util/api.php");
require_once(dirname(__FILE__)."/../util/http.php");
require_once(dirname(__FILE__)."/../util/common.php");
require_once(dirname(__FILE__)."/../ext/db_ext.php");
require_once(dirname(__FILE__)."/../model/website.php");
require_once(dirname(__FILE__)."/../model/urltag.php");
require_once(dirname(__FILE__)."/../model/product.php");

use ext\db_ext;
use util\api;
use util\http;
use model\website;
use model\urltag;
use model\product;

$GLOBALS['website']['id'] = 16;
$GLOBALS['fix_limits'] = 0;
db_ext::switch_db("jhbian_spider");



function dangdang_tag($std_urls="")
{
    $tag_types = urltag::get_types();
    $product_id = -1;
    $website = new website($GLOBALS['website']['id']);
    $std_urls = empty($std_urls) ? $website->get_html("", array("uid", "url")) : $std_urls;
    $url_hit = 0;
    while ($url_hit < count($std_urls)) {
        $url = $std_urls[$url_hit]['url'];
        //将接口中返回的手机URL抓换成PCURL, 以及不怎么标准的URL替换成规范的URL
        //http://product.dangdang.com/1134167307.html
        preg_match("/https?:\/\/product\.dangdang\.com\/(\d+)\.html.*/i", $url, $matches);
        if (isset($matches[1]) && !empty($matches[1])) {
            $product_id = $matches[1];
        } else {
            echo "Error parse the product id from url link $url!\n";
            $url_hit++;
            continue;
        }

        //设置代理和url-agent
        requests::$input_encoding="UTF-8";
        requests::$output_encoding = "UTF-8";
        requests::set_useragents(
               array(
                   'Mozilla/5.0 (Windows; U; Windows NT 5.2) Gecko/2008070208 Firefox/3.0.1',
                   'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0)',
                   'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13',
                   'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.12) Gecko/20080219 Firefox/2.0.0.12 Navigator/9.0.0.6',
                   'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Version/3.1 Safari/525.13',
                   'Mozilla/5.0 (iPhone; U; CPU like Mac OS X) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/4A93 Safari/419.3',
                   'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)',
                   'Mozilla/5.0 (Macintosh; PPC Mac OS X; U; en) Opera 8.0',)
           );
        $proxy_ip = api::getIp();
        if ($proxy_ip) {
           requests::set_proxies(
               array("http"=>$proxy_ip,
                      "https"=>$proxy_ip
               ));
           http::set_proxies(array(
               "http"=>$proxy_ip,
               "https"=>$proxy_ip
           ));
        } else {
            printf("%s\n","Error: A unexpected error occurred when get the proxy ip");
            log::error("Error: A unexpected error occurred when get the proxy ip");
        }
        $html_content = product::get_html($std_urls[$url_hit]['uid']);
        $tag_datas = array();
        $etag['ctime'] = date("Y-m-d h:i:s");
        $etag['ah_id'] = $std_urls[$url_hit]['uid'];
        //提取标签
        //从html中提取标签
        //提取配送标签(顺丰配送)
        $etag['uid'] = spawn_guid();
        $etag['type_name'] = $tag_types[3];
        $org_express_tag = trim(selector::select($html_content, "//div[contains(@class,'name_info')]/h2/span[contains(@class,'head_title_name')]"));
        if (!empty($org_express_tag) && !(mb_strrpos($org_express_tag, "顺丰") === false)) {
            $etag['tag_desc'] = '顺丰配送';
            $tag_datas[] = $etag;
        }

       //获取shopId,为api提供参数
        preg_match("/\"shopId\":\"(\d+)\"/im",$html_content,$filter_shopid);

        if (!empty($filter_shopid) && !empty($filter_shopid[1]) && is_numeric($filter_shopid[1])) {
            $shopId = $filter_shopid[1];
            //通过api获取信息
            //提取配送标签(免运费)api抓取
            //http://product.dangdang.com/index.php?r=%2Fcallback%2Fshipping&shopId=11350&areaId=1110104&templateId=0&type=1
            $tag_api_prefix = "http://product.dangdang.com/index.php?r=%2Fcallback%2Fshipping&shopId=";
            $tag_api_suffix = "&areaId=1110104&templateId=0&type=1";
            $tag_api = $tag_api_prefix.$shopId.$tag_api_suffix;
            $tag_api_info = requests::get($tag_api);
            $tag_api_info = empty($tag_api_info)?"":json_decode($tag_api_info,true);
            $etag['uid'] = spawn_guid();
            $etag['type_name'] = $tag_types[3];
            if (!empty($tag_api_info) && empty($tag_api_info['errMsg'])){
                $etag['tag_desc'] = mb_strrpos($tag_api_info['data'],'免运费') > 0 ? "免运费":"" ;
                $tag_datas[] = $etag;
            }
            //提取促销标签(限时抢购)api抓取
            //http://product.dangdang.com/index.php?r=callback%2Fproduct-info&productId=1167427258&isCatalog=0&shopId=7838
            $tag_api_prefix = "http://product.dangdang.com/index.php?r=callback%2Fproduct-info&productId=";
            $tag_api_suffix = "&isCatalog=0&shopId=";
            $tag_api = $tag_api_prefix.$product_id.$tag_api_suffix.$shopId;
            $tag_api_info = requests::get($tag_api);
            $tag_api_info = !empty($tag_api_info)? json_decode($tag_api_info,true):"";
            $etag['uid'] = spawn_guid();
            $etag['type_name'] = $tag_types[2];
            var_dump($tag_api_info);
            if (!empty($tag_api_info) && empty($tag_api_info['errMsg'])){
                $flash_sale=@$tag_api_info['data']['attachInfo']['iconTable']['p102']['title'];
                if(!empty($flash_sale) && mb_strrpos($flash_sale,"限时抢")) {
                    $etag['tag_desc'] = "限时抢购";
                    $tag_datas[] = $etag;
                }
            }
            //提取促销标签(店铺vip)api抓取
            //http://product.dangdang.com/index.php?r=callback%2Fproduct-info&productId=1167427258&isCatalog=0&shopId=7838
            $etag['uid'] = spawn_guid();
            $etag['type_name'] = $tag_types[2];
            if (!empty($tag_api_info) && empty($tag_api_info['errMsg'])){
                $shopVip=@$tag_api_info['data']['spu']['price']['shopVip'];
                if(!empty($shopVip) && intval($shopVip) == 1) {
                    $etag['tag_desc'] = "店铺vip";
                    $tag_datas[] = $etag;
                }
            }
        } else {
            printf("%s\n","Error: cannot get shopId in $url");
        }

        //提取经营标签(当当自营)
        $org_merchant_tag = trim(selector::select($html_content, "//div[contains(@class,'name_info')]/h1/@title"));
        if (mb_strrpos($org_merchant_tag, '当当自营')) {
            $etag['uid'] = spawn_guid();
            $etag['tag_desc'] = '当当自营';
            $etag['type_name'] = $tag_types[1];
            $tag_datas[] = $etag;
        }

        $tag_result = !empty($tag_datas) ? db::insert_batch("url_tag", $tag_datas) : -1;
        if ($tag_result === false) {
            printf("%s\n", "Error occurred in insert $url's tag into database'");
            $url_hit++;
            continue;
        }
        $url_hit++;
        echo "已完成" . round($url_hit / count($std_urls) * 100, 2) . "%\n";
    }
}

function dangdang_tag_fix() {
    if ($GLOBALS['fix_limits'] > 10) {
        return false;
    }
    $logfile = basename(__FILE__,'.php').'.log';
    $iter_logfile= basename(__FILE__,'.php').'.iter.log';

    $fpath = 'runstatus/'.$iter_logfile;
    if(!file_exists($fpath)) {
        $fpath = 'runstatus/'.$logfile;
    }
    if (!is_file($fpath) && !is_readable($fpath)) {
        printf("%s\n","File read error:$fpath don't exist!");
        return false;
    }
    $awk_command= "awk '{if(match($0,/\?id=([0-9]+)/,m)) {print m[1];print \",\" }if(match($0,/.*all_html_id is ([a-zA-Z0-9\-]+)/,m)) print m[1]; print \"\\n\"}' ORS=\"\""." $fpath";
    exec($awk_command,$awk_result,$status);
    $pro_awk_result = array();
    foreach($awk_result as $sub) {

        if(empty($sub)) {
            continue;
        }
        $sub_arr = mb_split(",",$sub,2);
        if (!empty($sub_arr)) {
            $pro_awk_result[] = $sub_arr;
        }
    }
    $awk_result = $pro_awk_result;
    if ($status != 0) {
        printf("%s\n", "Parse Error: Can't parse product id from $fpath log file!");
        return false;
    }

    if (count($awk_result) > 0) {
        $merge_urls = array();
        foreach($awk_result as $v) {
            $merge_urls[] = array(
                "uid" => $v[1],
                "url" => "http://item.dangdang.com/item.htm?id=".$v[0]
            );
        }
        dangdang_tag($merge_urls);
    }
    dangdang_tag_fix();
}

if (PHP_SAPI != 'cli') {
    printf("%s","This application can only run under cli!");
    exit(0);
}


if ($argc <= 1 ) {
    dangdang_tag();
} else {
    if($argv[1] == "get") {
        dangdang_tag();
    } else if ($argv[1] == "patch") {
        dangdang_tag_fix();
    } else {
        printf("%s","Argument Error!");
    }
}