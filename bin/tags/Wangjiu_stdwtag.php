<?php

/** 网酒标签分析程序
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

$GLOBALS['website']['id'] = 3;
$GLOBALS['fix_limits'] = 0;
db_ext::switch_db("jhbian_spider");

function format_data($data){
    $data= trim($data);
    $data = empty($data)?"":ltrim($data,"onSibRequestSuccess(");
    $data = empty($data)?"":rtrim($data,");");
    $data = empty($data)?"":json_decode($data,true);
    return $data;
}

function wangjiu_tag($std_urls="")
{
    $tag_types = urltag::get_types();
    $product_id = -1;
    $website = new website($GLOBALS['website']['id']);
    $std_urls = empty($std_urls) ? $website->get_html("", array("uid", "url")) : $std_urls;
    $url_hit = 0;
    while ($url_hit < count($std_urls)) {
        $url = $std_urls[$url_hit]['url'];
        $tag_datas = array();
        $etag['ctime'] = date("Y-m-d h:i:s");
        $etag['ah_id'] = $std_urls[$url_hit]['uid'];
        $product_id = -1;
        //提取商品id
        //http://www.wangjiu.com/mall/item-pid-HJvGeVcGL.html
        preg_match("/https?:\/\/www\.wangjiu\.com\/mall\/item-pid-([a-zA-Z0-9]+)\.html.*/i", $url, $matches);
        if (isset($matches[1]) && !empty($matches[1])) {
            $product_id = $matches[1];
        } else {
            echo "Error parse the product id from url link $url!\n";
            $url_hit++;
            continue;
        }
        //获取标签接口:
        //http://api.wangjiu.com/api/simple/promotionService/getProductPromotions?&pid=393569854&seller_id=0&select_info=gift,together,order_reduce,order_gift,order_add,order_discount&client_sig=pc&district_id=2126&format=jsonp&_=1503469329941
        $tag_api_prefix = "http://api.wangjiu.com/api/simple/promotionService/getProductPromotions?&pid=" ;
        $tag_api_suffix = "&seller_id=0&select_info=gift,together,order_reduce,order_gift,order_add,order_discount&format=text";
        $tag_api = $tag_api_prefix.$product_id.$tag_api_suffix;
        //获取接口内容
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
        $tag_api_info = requests::get($tag_api);
        $tag_api_info = !empty($tag_api_info)? json_decode($tag_api_info,true):"";

        if (empty($tag_api_info) || $tag_api_info['status'] == 0 ) {
            printf("%s\n","Error: Can not get the $url's tag info from api!");
            continue;
        }

        //提取促销标签(满减)
        $etag['uid'] = spawn_guid();
        $etag['type_name'] = $tag_types[2];
        $etag['tag_desc'] = !empty($tag_api_info['result']['order_reduce'])?"满减":"";
        if (!empty($etag['tag_desc'])) {
            $tag_datas[] = $etag;
        }
        //提取促销标签(赠品)
        $etag['uid'] = spawn_guid();
        $etag['type_name'] = $tag_types[2];
        $etag['tag_desc'] = !empty($tag_api_info['result']['gift'])&&intval($tag_api_info['result']['gift']['length'])> 0 ? "赠品" : "";
        if (!empty($etag['tag_desc'])) {
           $tag_datas[]  = $etag;
        }

        //提取促销标签(折扣)
        $etag['uid'] = spawn_guid();
        $etag['type_name'] = $tag_types[2];
        $etag['tag_desc'] = !empty($tag_api_info['result']['order_discount'])? "折扣" : "";
        if (!empty($etag['tag_desc'])) {
           $tag_datas[]  = $etag;
        }

        //提取促销标签(满赠)
        $etag['uid'] = spawn_guid();
        $etag['type_name'] = $tag_types[2];
        $etag['tag_desc'] = !empty($tag_api_info['result']['order_gift'])? "满赠" : "";
        if (!empty($etag['tag_desc'])) {
           $tag_datas[]  = $etag;
        }
        //提取促销标签(闪购)

        $etag['uid'] = spawn_guid();
        $etag['type_name'] = $tag_types[2];
        $etag['tag_desc'] = !empty($tag_api_info['result']['sub_type'])&&$tag_api_info['result']['sub_type'] = 3 ? "闪购":"";

        if (!empty($org_promote_tag)) {
           $tag_datas[]  = $etag;
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

function wangjiu_tag_fix() {
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
                "url" => "http://www.wangjiu.com/mall/item-pid-".$v[0].'.html'
            );
        }
        wangjiu_tag($merge_urls);
    }
    wangjiu_tag_fix();
}

if (PHP_SAPI != 'cli') {
    printf("%s","This application can only run under cli!");
    exit(0);
}


if ($argc <= 1 ) {
    wangjiu_tag();
} else {
    if($argv[1] == "get") {
        wangjiu_tag();
    } else if ($argv[1] == "patch") {
        wangjiu_tag_fix();
    } else {
        printf("%s","Argument Error!");
    }
}