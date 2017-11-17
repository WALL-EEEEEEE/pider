<?php
include_once("../app.php");
use Util\Api;
use Model\UrltagModel;
use Extension\DBExtension;
use Util\StructHtml\SearchEntry;
use Controller\WebsiteController;
use Model\ProductModel;
ini_set ('memory_limit', '2480M');  
$GLOBALS['website']['id'] = 1;

function get_coupon_tags ($api_content,&$tags) {
    if (empty($api_content['skuCoupon'])) {
        return false;
    }
    $coupon_tags = $api_content['skuCoupon'];
    if (!empty($coupon_tags)) {
        foreach ($coupon_tags as $coupon_tag) {
            $type = @$coupon_tag['couponType'];
            switch($type) {
            case 1 :
                $quota=@$coupon_tag['quota'];
                $discount = @$coupon_tag['discount'];
                $name = '满'.$quota.'减'.$discount;
                $tags[] = $name;
                break;
            default:
                break;
            }
    }
    }
    
}
function get_tags_from_api($product_ids) {
    foreach($product_ids as $product_id) {
        $tags = array();
        $api_url = "http://cd.jd.com/promotion/v2?skuId=".$product_id."&area=19_1607_3638_0&cat=12259%2C12260%2C9438";
        $result = null;
        printf("%s\n","Collecting tags from api ... ");
        Api::proxy_wrapper(function() use (&$result, $api_url){
            \requests::$input_encoding='UTF-8';
            \requests::$output_encoding='UTF-8';
            $result = requests::get($api_url);
            var_dump($api_url);
            var_dump($result);
        });
        $try_times = 0;
        $max_retry = 3;
        while(empty($result) && $try_times < $max_retry ) {
            printf("%s\n","Collecting tags from api ... failed, retry ".$try_times."/".$max_retry);
            Api::proxy_wrapper(function() use (&$result, $api_url){
                $result = requests::get($api_url);
                var_dump($api_url);
                var_dump($result);
            });
            $try_times++;
        }
        continue;
        if (empty($result)) {
            return false;
        }
        $raw_info = json_decode($result,true);
        if (!empty($raw_info['quan']['title'])) {
            $tags[] = array('tag_desc'=>'满额返券','tag_info'=>$raw_info['quan']['title']);
        }
        //Get the tags inside the pink bar which under product name
        if (!empty($raw_info['ads'])) {
            $ads_tags = $raw_info['ads'];
            foreach( $ads_tags as $tag) {
                if (!empty($tag['ad'])) {
                    $tag['ad'] = strip_tags($tag['ad']);
                    $tag['ad'] = str_replace(array('】','【'),' ',$tag['ad']);
                    //trip the empty elements
                    $tmp_tags = array_filter(preg_split('/[\s\t,;]+/',$tag['ad']),function($value){
                        if (empty($value)) {
                            return false;
                        }
                        return true;
                    });
                    $tags = count($tmp_tags) > 0 ? array_merge($tmp_tags,$tags):$tags;
                    unset($tmp_tags);
                }
            }
            printf("%s\n","Collecting tags from api ... done");
        }
        //Get the tags inside promotion box
        if (!empty($raw_info['prom']['tags'])) {
            $tmp2_tags = $raw_info['prom']['tags'];
            foreach($tmp2_tags as $tmp_tag) {
                $name = @$tmp_tag['name'];
                $content= @$tmp_tag['content'];
                if (empty($name)) {
                    continue;
                }
                if (empty($content)) {
                    $tags[] = $name;
                } else {
                    $tmp2_tag['tag_info'] = $content;
                    $tmp2_tag['tag_desc'] = $name; 
                    $tags[] = $tmp2_tag;
                }
            }

            if (!empty($raw_info['prom']['pickOneTag'])) {
                $tmp3_tags =  $raw_info['prom']['pickOneTag'];
                foreach($tmp3_tags as $tmp_tag) {
                    $name = @$tmp_tag['name'];
                    $content= @$tmp_tag['content'];
                    if (empty($name)) {
                        continue;
                    }
                    if (empty($content)) {
                        $tags[] = $name;
                    } else {
                        $tmp3_tag['tag_info'] = $content;
                        $tmp3_tag['tag_desc'] = $name; 
                        $tags[] = $tmp3_tag;
                    }
                }
            }
        }
        //Get the coupons tags
        get_coupon_tags($raw_info,$tags);
        var_dump($tags);
    }
}


$time_start = time();
$task = 1;
$getExtras = function($searchentry)  {
    $current_page = $searchentry->get_current_page();
    if ($current_page != -1) {
        $api_url = "http://search.jd.com/s_new.php?keyword=".$searchentry->keyword."&enc=utf-8&qrst=1&rt=1&stop=1&vt=2&suggest=1.his.0.0&page=".($current_page+1)."&s=29&scrolling=y&tpl=1_M";
        //                var_dump("Api URL:");
        //                var_dump($api_url);
        \requests::set_referer($searchentry->entry);
        \requests::$input_encoding="UTF-8";
        \requests::$output_encoding = "UTF-8";
        $api_result = \requests::get($api_url);
        $searchentry->extern(\selector::select($api_result,'//li/div/div/a/@href'));

    }
};

$searcher = new SearchEntry("http://search.jd.com/Search");
$search_urls = array();
$search_urls= $searcher->keyword_param('keyword')->extra_param('enc=utf-8')->totalpages('//div[@id=\'J_topPage\']/span/i')->search('葡萄酒','//div[@id="J_goodsList"]/ul/li/div/div/a/@href')->iterate($getExtras)->reset_totalpages(2,'*')->skip('even')->go();
$website = new WebsiteController($GLOBALS['website']['id']);
$website->suffix_product_url('.html');
$website->prefix_product_url('http://item.jd.com/');
//https://item.jd.com/12098230917.html
$url_format  = '/\/\/item\.jd\.com\/(\d+)\.html/i';
$search_urls = $website->format_urls($search_urls,$url_format,function($url){
    if (strpos($url,'http') !== false){
        return $url;
    }
    return 'http:'.$url;
});

$search_urls = array_slice($search_urls,0,100);
$product_ids = $website->parse_product_id($search_urls,'/https?:\/\/item\.jd\.com\/(\d+)\.html/i');


    /**
     $search_urls = array(
        'http://item.jd.com/16299250454.html',
        'http://item.jd.com/10124414717.html',
        'http://item.jd.com/10189569472.html',
        'http://item.jd.com/1304924.html'
    );
    **/
//slice the url into $task pieces
$gap = (int)round(count($search_urls)/$task);
//create a share memory segment to store processs ids
$process_pool = array();
$process_pool_key = ftok(__FILE__,'0');
$process_pool_shm = shmop_open($process_pool_key,'c',0644,1024);
//spawn child process
for( $i = 0; $i < $task; $i++ ) {
    $urls = array_slice($search_urls,$gap*$i,$gap);
    $pid = pcntl_fork();
    if ($pid == -1 ) {
        die("Counld not fork!");
    }
    if($pid !== 0 ) {
        $process_pool[$pid] = $pid;
        shmop_write($process_pool_shm,serialize($process_pool),0);
    }
    if($pid == 0) {
        get_tags_from_api($product_ids);
        exit(0);
    }
}
