<?php
namespace  Controller;

use  Util\StructHtml\MultiTabs;
use  Extension\Controller;
use  Model\ProductModel;
use  requests;

/** website controlller use to handle logitcal about website
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/9/5
 * Time: 17:18
 */

class WebsiteController extends Controller
{
    /**
     * website types
     */
    const MULTITABS = 1;
    const SEARCHENTRY = 2;

    public $type = null;
    public $website_id = null;
    public $url_format = null;
    public $product_url_prefix = '';
    public $product_url_suffix = '';

    public function __construct($website_id, $type = 0 )
    {
        if (empty($website_id)) {
           return false;
        }
        $this->website_id = $website_id;
        if (!empty($type)) {
            $this->type = $type;
        }
    }

    /**
     * 设置网站布局类型,比如multi-tabs,search-entries,等等，方便根据网站布局处理
     */
    public function  set_type($type) {
        $this->type = $type;
    }


    /**
     * 设置规范url的正则表达式
     */
    public function set_url_format($url_format) {
        $this->url_format = $url_format;
    }
    public function get_html() {
    }

    /**
     * 同步更新商品相关的html
     * @param $urls
     * @return array|bool 返回所有url相关的all_html->uid和url_tag->uid的关联数组
     */
   public function sync_html($products_id) {
        $assoc_arrs = array();
        if (is_string($products_id)) {
            $products_id= array($products_id);
        }
        if (is_array($products_id)) {
            $counter=0;
            $total=count($products_id);
            $pid_it = new \ArrayIterator($products_id);
            $max_retry = 10;
            $retry_time = 0;
            printf("%s\n","Syncing html $counter/$total");
            while($pid_it->valid()) {
                $product_id = $pid_it->current();
                $product = new ProductModel($product_id,$this->website_id);
                $all_html_uid   = $product->get_html_uid();
                $product_c = new ProductController($product_id,$this->website_id);
                if (!empty($this->product_url_suffix)) {
                   $product_c->set_url_suffix($this->product_url_suffix) ;
                }
                if (!empty($this->product_url_prefix)) {
                    $product_c->set_url_prefix($this->product_url_prefix);
                }
                //如果all_html表里里有商品的html,更新，直接关联标签
                if (!empty($all_html_uid)) {
                    $status = $product_c->update_html();
                    if ($status) {
                        $assoc_arrs[$product_id] = $all_html_uid;
                    } else {
                        printf("%s\n","Syncing: Update html error! retrying ".$retry_time.'/'.$max_retry);
                        if ($retry_time <= $max_retry) {
                            $retry_time++;
                        } else {
                            $pid_it->next();
                            $retry_time=0;
                        }
                        continue;
                    }
                } else {
                    //如果all_html表里没有商品的html，新增,并将标签关联html
                    $status = $product_c->add_html();
                    if ($status!== false) {
                        $assoc_arrs[$product_id]  = $status;
                    } else {
                        printf("%s\n","Syncing: Add html error! retrying ".$retry_time.'/'.$max_retry);
                        if ($retry_time <= $max_retry) {
                            $retry_time++;
                        } else {
                            $pid_it->next();
                            $retry_time= 0;
                        }
                        continue;
                    }
                }
                $counter++;
                $pid_it->next();
                printf("%s\n","Syncing html $counter/$total");
            }
        }
        if (count($assoc_arrs) > 0) {
            return $assoc_arrs;
        }
        return false;
    }

    /**
     *
     * 同步更新商品id相关的商品详情
     * Syncing the products details
     *
     */
    public function sync_details($product_ids) {
    }

    public function parse_product_id($urls,$url_format, $is_log = false) {
        $product_ids= array();
        if (is_string($urls)) {
            $urls = array($urls);
        }
        foreach ($urls as $key => $url) {
            if ($url_format) {
                preg_match($url_format,$url,$product_id);
                $product_id = @$product_id[1];
                if (!empty($product_id)) {
                    $product_ids[] = $product_id;
                    if ($is_log) {
                        printf("%s\n","Error: Parse product_id from url $url!");
                    }
                }
            } else {
                throw new \ErrorException('Unkown url format is set!');
            }
        }

        if (!empty($product_ids)) {
            return $product_ids;
        } else {
            return false;
        }

    }

    public function prefix_product_url($string) {
       $this->product_url_prefix = $string;
    }

    public function suffix_product_url($string) {
       $this->product_url_suffix = $string;
    }

   public function format_urls ($urls, $url_format, $callback = '', $is_log = false) {
        if (is_string($urls)) {
            $urls = array($urls);
        }
        foreach ($urls as $key => $url) {
            if ($url_format) {
                $is_valid = preg_match($url_format,$url,$product_id);
                if (!$is_valid) {
                    unset($urls[$key]);
                    if ($is_log) {
                        printf("%s\n","Error: url $url invalid!");
                    }
                }else {
                    if (!empty($callback) && is_callable($callback)) {
                        //去除url中query语句
                        $trimpos = strpos($url,'?');
                        $url = $trimpos === false? $url:substr($url,0,$trimpos);
                        $urls[$key] = $callback($url);
                        //去除掉重复的酒链接
                        $urls = array_unique($urls);
                    }
                }
            } else {
                throw new \ErrorException('Unkown url format is set!');
            }
        }
        if (count($urls) > 0) {
            return $urls;
        } else {
            return false;
        }
    }


    public function get_flash_by_api($url,$fields){
        $tag_datas = array();
        $api_url_prefix ="http://api.wangjiu.com/api/simple/flashRecommendService/getFlashRecommendByDate?recommend_category=4&query_date=";
        $today = date("Y-m-d");
        $api_url = $api_url_prefix.$today;
        $cookie_url = "http://authentication.wangjiu.com/api/list/generateSession.jsonp";
        requests::set_useragents(
            array(
                'Mozilla/5.0 (Windows; U; Windows NT 5.2) Gecko/2008070208 Firefox/3.0.1',
                'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0)',
                'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13',
                'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.12) Gecko/20080219 Firefox/2.0.0.12 Navigator/9.0.0.6',
                'Mozilla/5.0 (Windows; U; Windows NT 4.2) AppleWebKit/525.13 (KHTML, like Gecko) Version/3.1 Safari/525.13',
                'Mozilla/5.0 (iPhone; U; CPU like Mac OS X) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/4A93 Safari/419.3',
                'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)',
                'Mozilla/5.0 (Macintosh; PPC Mac OS X; U; en) Opera 8.0',)
        );
        //获取api中的闪购酒款
        //获取cookie,为获取api中的闪购酒款做准备
        $cookie_info = requests::get("http://authentication.wangjiu.com/api/list/generateSession.jsonp");
        $cookie_info = !empty($cookie_info)?ltrim($cookie_info,'('):'';
        $cookie_info = !empty($cookie_info)?rtrim($cookie_info,');'):'';
        $cookie_info = !empty($cookie_info)?json_decode($cookie_info,true):"";
        //为请求设置cookie
        if (empty($cookie_info) || intval($cookie_info['status']) != 1) {
            printf("%s\n","Error: can't get cookie!");
        }
        $cookies = @$cookie_info['result'][0]['COOKIE_LINKDATA'];
        if (empty($cookies)) {
            printf("%s\n","Error: can't parse cookie from cookie info!");
        }
        //重新组织得到的cookie数据
        $cookies = explode('&',$cookies);
        $cookies = implode(";",$cookies);
        requests::set_cookies($cookies);
        $flash_promotion_info = requests::get($api_url);
        if(empty($flash_promotion_info)) {
            printf("%s\n","Error when gettingg the flash promotion info from api $api_url!");
        }
        $flash_promotion_info = json_decode($flash_promotion_info,true);
        $flash_products = @$flash_promotion_info['result'][0]['flash_product'];
        if (empty($flash_products)) {
            printf("%s\n","Error when getting the flash product from api!");
        }
        $tag_data['type_name'] = '促销标签';
        $tag_data['tag_desc'] = "闪购";
        $tag_data['ctime'] = date('Y-m-d h:i:s');

        //整理从api中获取的闪购标签数据
        if (!empty($flash_products) && is_array($flash_products)) {
            $counter = 0;
            $total = count($flash_products);
            foreach($flash_products as $product) {
                printf("%s\n",'Collecting the tags from api ...  '.$counter.'/'.$total);
                $tag_data['uid'] = spawn_guid();
                //获取对应的all_html得uid
                $product_id = @$product['detail_product_id'];
                if (empty($product_id)) {
                    printf("%s\n","Error: can't get product_id from api info!");
                    continue;
                } else {
                    //判断all_html
                    $product = new product($product_id,$this->website_id);
                    $all_html_id   = $product->get_html_uid();
                    if (empty($all_html_id)) {
                        printf("%s\n","Error: product $product_id is invalid! ");
                        continue;
                    }
                    $tag_data['ah_id'] = $all_html_id;
                    $tag_datas[] = $tag_data;
                    $counter++;
                }
            }
        }
        return $tag_datas;
    }

    /**
     * 获取闪购商品
     * @param string|array $urls 闪购页面
     * @selectors array|string  抓取页面的url的选择器
     * @param string|array $subtabs  网页内嵌tab页面
     * @param string $tabtype 'slash|query' 网页内嵌tab页面的链接拼接形式
     * @return array|bool
     */
    public function get_flash_by_html($urls,$selectors,$subtabs='',$tabtype='slash',$baseurl=''){
        $tag_datas = array();
        $flash_tag_urls = multitabs::get_all_elements($urls,$selectors,$subtabs,$tabtype,$baseurl);
        $tag_data['type_name'] = '促销标签';
        $tag_data['tag_desc'] = "闪购";
        $tag_data['ctime'] = date('Y-m-d h:i:s');
        //整理html中获取的闪购标签数据
        if (!empty($flash_tag_urls)) {
            $flash_tag_urls = format_urls($flash_tag_urls,$this->url_format,function($url){
                if (strpos($url,'http') !== false){
                    return $url;
                }
                return 'http:'.$url;
            });
            printf("%s\n","Syncing html...");
            $assoc_arrs = sync_html($flash_tag_urls);
            printf("%s\n","Syncing html... done");
            if ($assoc_arrs) {
                $counter = 0;
                $total = count($assoc_arrs);
                foreach($assoc_arrs as $product_id => $all_html_id) {
                    $tag_data['uid'] = spawn_guid();
                    $tag_data['ah_id'] = $all_html_id;
                    $tag_datas[] = $tag_data;
                    $counter++;
                }
            }
        }
        return $tag_datas;
    }

    /** 获取限时抢购的商品
     * @param $urls
     * @param $selectors
     * @param string $subtabs
     * @param string $tabtype
     * @param string $baseurl
     * @return array
     */
    public function get_rush_by_html($urls,$selectors,$subtabs='',$tabtype='slash',$baseurl=''){

        $tag_datas = array();
        $rush_tag_urls = multitabs::get_all_elements($urls,$selectors,$subtabs,$tabtype,$baseurl);
        $tag_data['type_name'] = '促销标签';
        $tag_data['tag_desc'] = "限时抢购";
        $tag_data['ctime'] = date('Y-m-d h:i:s');
        //整理html中获取的闪购标签数据
        if (!empty($rush_tag_urls)) {
            $rush_tag_urls = format_urls($rush_tag_urls,$this->url_format,function($url){
                if (strpos($url,'http') !== false){
                    return $url;
                }
                return 'http:'.$url;
            });
            printf("%s\n","Syncing html...");
            $assoc_arrs = sync_html($rush_tag_urls);
            printf("%s\n","Syncing html... done");
            if ($assoc_arrs) {
                $counter = 0;
                $total = count($assoc_arrs);
                foreach($assoc_arrs as $product_id => $all_html_id) {
                    printf("%s\n",'Collecting the tags from html ...  '.$counter.'/'.$total);
                    $tag_data['uid'] = spawn_guid();
                    $tag_data['ah_id'] = $all_html_id;
                    $tag_datas[] = $tag_data;
                    $counter++;
                }
            }
        }
        return $tag_datas;
    }

    public function updateDetails($product_details) {
        $model = self::model();
        $model->updateDetail($product_details);
    }

    public function updateUrls ($product_urls) {
        $model = self::model();
        $model->updateUrls($product_urls);
    }

}
