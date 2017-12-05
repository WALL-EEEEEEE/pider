<?php

/** 亚马逊标签分析程序
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

$GLOBALS['website']['id'] = 9;
$GLOBALS['fix_limits'] = 0;
db_ext::switch_db("jhbian_spider");

function format_data($data){
    $data= trim($data);
    $data = empty($data)?"":ltrim($data,"onSibRequestSuccess(");
    $data = empty($data)?"":rtrim($data,");");
    $data = empty($data)?"":json_decode($data,true);
    return $data;
}

function amazon_tag($std_urls="")
{
    $tag_types = urltag::get_types();
    $product_id = -1;
    $website = new website($GLOBALS['website']['id']);
    $std_urls = empty($std_urls) ? $website->get_html("", array("uid", "url")) : $std_urls;
    $url_hit = 0;
    while ($url_hit < count($std_urls)) {
        $url = $std_urls[$url_hit]['url'];
        //将接口中返回的手机URL抓换成PCURL, 以及不怎么标准的URL替换成规范的URL
        //http:\/\/item.amazon.com\/item.htm?id=20172326620
        //http:\/\/h5.m.amazon.com\/awp\/core\/detail.htm?id=20172326620
        preg_match("/https?:\/\/www\.amazon\.cn\/dp\/([a-zA-Z0-9]+).*/i", $url, $matches);
        if (isset($matches[1]) && !empty($matches[1])) {
            $product_id = $matches[1];
        } else {
            echo "Error parse the product id from url link $url!\n";
            $url_hit++;
            continue;
        }
        $html_content = product::get_html($std_urls[$url_hit]['uid']);
        $tag_datas = array();

        $etag['ctime'] = date("Y-m-d h:i:s");
        $etag['ah_id'] = $std_urls[$url_hit]['uid'];
        //提取标签
        //提取配送标签
        $etag['uid'] = spawn_guid();
        $etag['type_name'] = $tag_types[3];
        $org_express_tag = trim(selector::select($html_content, "//span[@id='price-shipping-message']/strong[1]"));
        $etag['tag_desc'] = empty($org_express_tag) ? "" : $org_express_tag;
        if (!empty($org_express_tag) && mb_strrpos($org_express_tag, "免运费") !== false) {
            $tag_datas[] = $etag;
        }

        //提取促销标签
        $etag['uid'] = spawn_guid();
        $etag['type_name'] = $tag_types[2];
        $org_promote_tag = selector::select($html_content, "//span[contains(@class,'apl_type')]//*/text()");
        $org_promote_tag = trim($org_promote_tag, ' n\t\n');
        $etag['tag_desc'] = empty($org_promote_tag) ? "" : $org_promote_tag;
        //整理标签内容数据，准备插入数据库
        if (!empty($org_promote_tag)) {
            $tag_datas[] = $etag;
        }

        //提取经营标签
        $org_merchant_tag = trim(selector::select($html_content, "//span[@id='ddmMerchantMessage']"));
        if (mb_strrpos($org_merchant_tag, '亚马逊直接销售')) {
            $etag['uid'] = spawn_guid();
            $etag['tag_desc'] = '亚马逊直营';
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

function amazon_tag_fix() {
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
                "url" => "http://item.amazon.com/item.htm?id=".$v[0]
            );
        }
        amazon_tag($merge_urls);
    }
    amazon_tag_fix();
}

if (PHP_SAPI != 'cli') {
    printf("%s","This application can only run under cli!");
    exit(0);
}


if ($argc <= 1 ) {
    amazon_tag();
} else {
    if($argv[1] == "get") {
        amazon_tag();
    } else if ($argv[1] == "patch") {
        amazon_tag_fix();
    } else {
        printf("%s","Argument Error!");
    }
}