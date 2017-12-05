<?php
/**
 * 测试使用curl命令行抓淘宝api是否效率更高
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/8/17
 * Time: 17:56
 */
require_once(dirname(__FILE__)."/../phpspider/core/init.php");
require_once(dirname(__FILE__)."/../util/api.php");
require_once(dirname(__FILE__)."/../util/http.php");
require_once(dirname(__FILE__)."/../util/common.php");
require_once(dirname(__FILE__)."/../ext/db_ext.php");
require_once(dirname(__FILE__)."/../model/website.php");
require_once(dirname(__FILE__)."/../model/urltag.php");

use ext\db_ext;
use util\api;
use util\http;
use model\website;
use model\urltag;
/** 淘宝的标签分析程序
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/8/14
 * Time: 15:56
 *
 */
$GLOBALS['website']['id'] = 11;
$GLOBALS['fix_limits'] = 0;
db_ext::switch_db("jhbian_spider");

function format_data($data){
        $data= trim($data);
        $data = empty($data)?"":ltrim($data,"onSibRequestSuccess(");
        $data = empty($data)?"":rtrim($data,");");
        $data = empty($data)?"":json_decode($data,true);
        return $data;
}

function taobao_tag($std_urls=""){
    $tag_types = urltag::get_types();
    $tag_api_prefix = "https://detailskip.taobao.com/service/getData/1/p1/item/detail/sib.htm?itemId=";
    $tag_api_suffix = "&modules=price,dynStock,qrcode,viewer,price,duty,xmpPromotion,delivery,upp,activity,fqg,zjys,couponActivity,soldQuantity,originalPrice,tradeContract&callback=onSibRequestSuccess";
    $product_id = -1;
    $website = new website($GLOBALS['website']['id']);
    $std_urls = empty($std_urls)?$website->get_html("",array("uid","url")):$std_urls;
    $failed_max_try = 5;
    $url_hit = 0;
    while ( $url_hit < count($std_urls)) {
        $url = $std_urls[$url_hit]['url'];
        requests::$input_encoding = "GBK";
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
        }
        //将接口中返回的手机URL抓换成PCURL, 以及不怎么标准的URL替换成规范的URL
        //http:\/\/item.taobao.com\/item.htm?id=20172326620
        //http:\/\/h5.m.taobao.com\/awp\/core\/detail.htm?id=20172326620
        $url = preg_replace("/https?:\/\/h5\.m\.taobao\.com\/awp\/core\/detail\.htm\?.*id=(\d+).*/i","http://item.taobao.com/item.htm?id=\\1",$url);
        $url = preg_replace("/https?:\/\/item\.taobao\.com\/item\.htm\?.*id=(\d+).*/i","http://item.taobao.com/item.htm?id=\\1",$url);
        preg_match("/https?:\/\/item\.taobao\.com\/item\.htm\?id=(\d+)/i",$url,$matches);
        if (isset($matches[1]) && !empty($matches[1])) {
            $product_id = $matches[1];
        } else {
            echo "Error parse the product id from url link $url!\n";
            $url_hit++;
            continue;
        }
        $proxy_ip = ltrim($proxy_ip,"http://");
        $tag_api = $tag_api_prefix.$product_id.$tag_api_suffix;
        $curl_referer=" -H 'referer:".$url."'";
        $curl_command = "curl -LX GET -x ".$proxy_ip.$curl_referer." '".$tag_api."' 2>/dev/null 1>curl.tmp &&  cat curl.tmp";
        $tag_info=shell_exec($curl_command);

        $tag_info = format_data($tag_info);
        //为避免因为ip质量问题而导致的大量的商品链接抓取不到，当发现链接无法抓取到后，进行切换ip尝试
        if (empty($tag_info) || !isset($tag_info['data'])) {
            //ip接口10s切换一次ip
            //尝试切换ip再次重新抓,最多尝试5次
            $failed_times_try = 1;
            while ($failed_max_try > $failed_times_try && (empty($tag_info) || !isset($tag_info['data']))) {
                sleep(10);
                $proxy_ip=api::getIp();
                $curl_command = "curl -LX GET -x ".$proxy_ip.$curl_referer." '".$tag_api."' 2>/dev/null 1>curl.tmp &&  cat curl.tmp";
                $tag_info = shell_exec($curl_command);
                $tag_info = format_data($tag_info);
                printf("%s\n","Failed try: $failed_times_try/$failed_max_try");
                $failed_times_try++;
            }
            if (empty($tag_info) || !isset($tag_info['data'])) {
                $url_hit++;
                printf("%s\n","Error occurred when get the tag api of $url, all_html_id is {$std_urls[$url_hit]['uid']}!");
                continue;
            }

        }

        //整理标签内容数据，准备插入数据库
        $tag_datas = array();
        $tag1_data['uid'] = spawn_guid();
        $tag1_data['tag_desc'] = isset($tag_info['data']['deliveryFee']['data']['serviceInfo']['list'][0]['info']) && !empty($tag_info['data']['deliveryFee']['data']['serviceInfo']['list'][0]['info']) ? @$tag_info['data']['deliveryFee']['data']['serviceInfo']['list'][0]['info'] : "";
        $tag1_data['ah_id'] = $std_urls[$url_hit]['uid'];
        $tag1_data['type_name'] = $tag_types[3];
        $tag1_data['ctime'] = date("Y-M-d h:m:s");
        if (!empty($tag1_data['tag_desc']) && mb_strrpos($tag1_data['tag_desc'],"免运费") !== false) {
            $tag_datas[] = $tag1_data;
        }
        $tag2_data['uid'] = spawn_guid();
        $tag2_data['tag_desc'] = isset($tag_info['data']['promotion']['promoData']['def'][0]['type']) && !empty($tag_info['data']['promotion']['promoData']['def'][0]['type'])?@$tag_info['data']['promotion']['promoData']['def'][0]['type']:"";
        $tag2_data['ah_id'] = $std_urls[$url_hit]['uid'];
        $tag2_data['type_name'] = $tag_types[2];
        $tag2_data['ctime'] = date("Y-M-d h:m:s");
        if (!empty($tag2_data['tag_desc'])) {
            $tag_datas[] = $tag2_data;
        }
        $tag_result = !empty($tag_datas)?db::insert_batch("url_tag",$tag_datas):-1;
        if ($tag_result === false) {
            printf("%s\n", "Error occurred in insert $url's tag into database'");
            $url_hit++;
            continue;
        }

        $url_hit++;
        echo "已完成".round($url_hit/count($std_urls)*100,2)."%\n";
    }
}

function taobao_tag_fix() {
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
                "url" => "http://item.taobao.com/item.htm?id=".$v[0]
            );
        }
        taobao_tag($merge_urls);
    }
    taobao_tag_fix();
}

if (PHP_SAPI != 'cli') {
    printf("%s","This application can only run under cli!");
    exit(0);
}


if ($argc <= 1 ) {
    taobao_tag();
} else {
    if($argv[1] == "get") {
        taobao_tag();
    } else if ($argv[1] == "patch") {
        taobao_tag_fix();
    } else {
        printf("%s","Argument Error!");
    }
}