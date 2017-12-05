<?php

/** 苏宁标签分析程序
 * Fuck suning!
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

$GLOBALS['website']['id'] = 21;
$GLOBALS['fix_limits'] = 0;
db_ext::switch_db("jhbian_spider");

function suning_tag($std_urls="")
{
    $tag_types = urltag::get_types();
    $product_id = -1;
    $website = new website($GLOBALS['website']['id']);
    $std_urls = empty($std_urls) ? $website->get_html("", array("uid", "url")) : $std_urls;
    $url_hit = 0;
    while ($url_hit < count($std_urls)) {
        $url = $std_urls[$url_hit]['url'];
        //将接口中返回的手机URL抓换成PCURL, 以及不怎么标准的URL替换成规范的URL
        //http://product.suning.com/1134167307.html
        preg_match("/https?:\/\/product\.suning\.com\/(\d+)\.html.*/i", $url, $matches);
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
        $org_express_tag = trim(selector::select($html_content, "//div[contains(@class,'name_info')]/h2"));
        var_dump($html_content);
        var_dump($org_express_tag);
        if (!empty($org_express_tag) && !(mb_strrpos($org_express_tag, "顺丰") === false)) {
            $etag['tag_desc'] = '顺丰配送';
            $tag_datas[] = $etag;
        }
        var_dump($etag);

       //获取请求api需要的参数
        $script_info = selector::select($html_content,"/html/head/script[9]");
        $script_info = !empty($script_info)? json_encode($script_info,true):"";
        $part_number = $script_info['partNumber'];
        $icode = "_0000000000_190_020_0200101_500353_1000041_9041_10274_Z001___";
        $weight = $script_info['weight'];
        $vendor_code = $script_info['vendorCode'];
        $catenids = $script_info['catenIds'];
        $api_key = !empty($part_number)&&!empty($icode)&&!empty($weight)&&!empty($vendor_code);

        //拼凑api链接
        if ($api_key) {
            $api_prefix = "https://pas.suning.com/nspcsale_0_";
            $api_url = $api_prefix . $part_number . "_" . $part_number . "_" . $vendor_code . $icode . $catenids . '_' . $weight . "_0.html";
            //通过api获取信息
            $tag_api_info = requests::get($api_url);
            $tag_api_info = !empty($tag_api_info) ? ltrim($tag_api_info, 'pcData(') : "";
            $tag_api_info = !empty($tag_api_info) ? rtrim($tag_api_info, ')') : "";
            $tag_api_info = !empty($tag_api_info) ? json_decode($tag_api_info, true) : "";
            $promotion_type =  @$tag_api_info['data']['price']['saleInfo'][0]['priceType'];
            $freight_type = @$tag_api_info['data']['prescription']['serviceLabel'];
            $freight_punc = @$tag_api_info['data']['prescription']['zsdLabel'];

            //提取配送标签(次日达，半日达)
            if (!empty($freight_type) && is_numeric($freight_type)) {
                $etag['uid'] = spawn_guid();
                $etag['type_name'] = $tag_types[3];

                switch ($freight_type) {
                    case 60:
                        $tag['tag_desc'] = "半日达";
                        break;
                    case 61:
                        $tag['tag_desc'] = '次日达';
                        break;
                    default:
                        break;
                }
                $tag_datas[] = $etag;
            }
            //提取配送标签(准时达)
            if (!empty($freight_punc) && is_numeric($freight_punc)) {
                $etag['uid'] = spawn_guid();
                $etag['type_name'] = $tag_types[3];
                if ($freight_punc == 70) {
                    $etag['type_desc'] = "准时达";
                }
                $tag_datas[] = $etag;
            }

            //提取促销标签(限时促销)
            if  (!empty($promotion_type) && $promotion_type=='1') {
                 $etag['uid'] = spawn_guid();
                $etag['type_name'] = $tag_types[3];
                if ($freight_punc == 70) {
                    $etag['type_desc'] = "限时促销";
                }
                $tag_datas[] = $etag;
            }
            //提取促销标签(大聚惠)
            if  (!empty($promotion_type) && $promotion_type=='4-1') {
                $etag['uid'] = spawn_guid();
                $etag['type_name'] = $tag_types[3];
                if ($freight_punc == 70) {
                    $etag['type_desc'] = "大聚惠";
                }
                $tag_datas[] = $etag;
            }
            //提取配送标签(急速达),@TODO
        }
        //提取促销标签(整箱优惠,等等...)
        $org_promotion_tag = selector::select($html_content,"//h2[@id='promotionDesc']");
        $org_promotion_tag = trim($org_promotion_tag);
        $etag['uid'] = spawn_guid();
        $etag['type_name'] = $tag_types[2];
        if (!empty($org_promotion_tag)) {
            $etag['tag_desc'] = $org_promotion_tag;
            $tag_datas[] = $etag;
        }
        //提取经营标签(苏宁自营)
        $org_merchant_tag = trim(selector::select($html_content, "//span[@id='itemNameZy']"));
        if (mb_strrpos($org_merchant_tag, '自营')) {
            $etag['uid'] = spawn_guid();
            $etag['tag_desc'] = '苏宁自营';
            $etag['type_name'] = $tag_types[1];
            $tag_datas[] = $etag;
        }
        //提取经营标签(苏宁超市)
        $org_merchant_tag = trim(selector::select($html_content, "//h1[@id='itemDisplayName']"));
        if (mb_strrpos($org_merchant_tag, '苏宁超市')) {
            $etag['uid'] = spawn_guid();
            $etag['tag_desc'] = '苏宁超市';
            $etag['type_name'] = $tag_types[1];
            $tag_datas[] = $etag;
        }

        $tag_result = !empty($tag_datas) ? db::insert_batch("url_tag", $tag_datas) : -1;
        if ($tag_result === false) {
            printf("%s\n", "Error occurred in insert $url's tag into database'");
            $url_hit++;
            continue;
        }
        //同步all_html中的analysis_flag
        $sync_flag_sql = "update all_html set analysis_flag = 1 where uid=".$etag['ah_id'];
        $sync_flag =  db::update($sync_flag_sql);
        $url_hit++;
        echo "已完成" . round($url_hit / count($std_urls) * 100, 2) . "%\n";
    }
}

function suning_tag_fix() {
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
                "url" => "http://item.suning.com/item.htm?id=".$v[0]
            );
        }
        suning_tag($merge_urls);
    }
    suning_tag_fix();
}

if (PHP_SAPI != 'cli') {
    printf("%s","This application can only run under cli!");
    exit(0);
}


if ($argc <= 1 ) {
    suning_tag();
} else {
    if($argv[1] == "get") {
        suning_tag();
    } else if ($argv[1] == "patch") {
        suning_tag_fix();
    } else {
        printf("%s","Argument Error!");
    }
}