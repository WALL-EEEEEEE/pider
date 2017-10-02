<?php
namespace Controller;
use Util\Http;
use Util\Api;
use Extension\Controller;
use Model\ProductModel;

/**
 * Class product
 * @package controller
 */
class ProductController extends Controller{

    private $website_id = -1;
    private $product_id = -1;
    private $if_validate = false;
    public $product_url = '';
    public $url_prefix = '';
    public $url_suffix = '';
    public $valid_url = '';

    public function __construct($product_id,$website_id, $if_validate = false)
    {
        $this->website_id = $website_id;
        $this->product_id = $product_id;
        $this->if_validate = $if_validate;
    }

    public function set_product_url ($url) {
        $this->validate_url($url);
    }

    public function set_url_prefix($prefix) {
        $this->url_prefix = $prefix;
    }
    public function set_url_suffix($suffix) {
        $this->url_suffix = $suffix;
    }
    //add html for product
    public function add_html($encoding="UTF-8") {
        $data = array();
        \requests::$input_encoding = $encoding;
        \requests::$output_encoding = $encoding;
        if (!empty($this->url_suffix)) {
            $this->product_url = empty($this->product_url)?$this->product_id.$this->url_suffix:$this->product_url.$this->url_suffix;
        }
        if (!empty($this->url_prefix)) {
            $this->product_url = empty($this->product_url)?$this->url_prefix.$this->product_id:$this->url_prefix.$this->product_url;
        }
        $url = $this->product_url;
        if ($this->if_validate) {
            $url = $this->validate_url($url);
        }
        \requests::set_useragents(
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
            \requests::set_proxies(
                array("http"=>$proxy_ip,
                    "https"=>$proxy_ip
                ));
        } else {
            printf("%s\n","Error: A unexpected error occurred when get the proxy ip");
        }
        $html_content = \requests::get($url);
        if (!empty($html_content)) {
            $data['uid'] = spawn_guid();
            $data['ctime'] =  date("Y-m-d h:i:s");
            $data['url'] = $url;
            $data['sku_id'] = $this->product_id ;
            $data['website_id'] = $this->website_id;
            $data['runtime'] = date("Y-m-d h:i:s");
            $header = http::get_http_header($url);
            if ($header) {
                $data['modify_date'] = @$header['last_modfied'];
                $data['content_length'] = @$header['content_length'];
                $data['etag'] = @$header['etag'];
            }
            //update the data in database
            $result = \db::insert('all_html',$data);
            //update the html on the disk
            if (!empty($GLOBALS['env'])) {
                $hresult = writeHtml($data['uid'],$html_content,'/data/html/'.$GLOBALS['env'].'/');
            } else {

                $hresult = writeHtml($data['uid'],$html_content);
            }

            if($result !== false && $hresult) {
                return $data['uid'];
            }
            return false;

        } else {
            return false;
        }
    }

    //update html for product
    public function update_html($encoding= "UTF-8"){
        $data = array();
        \requests::$input_encoding = $encoding;
        \requests::$output_encoding = $encoding;
        if (!empty($this->url_suffix)) {
            $this->product_url = empty($this->product_url)?$this->product_id.$this->url_suffix:$this->product_url.$this->url_suffix;
        }
        if (!empty($this->url_prefix)) {
            $this->product_url = empty($this->product_url)?$this->url_prefix.$this->product_id:$this->url_prefix.$this->product_url;
        }
        $url = $this->product_url;
        if ($this->if_validate) {
            $url = $this->validate_url($url);
        }
        \requests::set_useragents(
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
            \requests::set_proxies(
                array("http"=>$proxy_ip,
                    "https"=>$proxy_ip
                ));
        } else {
            printf("%s\n","Error: A unexpected error occurred when get the proxy ip");
        }
        $html_content = \requests::get($url);
        if (!empty($html_content)) {
            $data['url'] = $url;
            $data['runtime'] = date("Y-m-d h:i:s");
            $header = http::get_http_header($url,'GET',3);
            if ($header) {
                $data['modify_date'] = @$header['last_modfied'];
                $data['content_length'] = @$header['content_length'];
                $data['etag'] = @$header['etag'];
            }
            //update the data in database
            $result = \db::update('all_html',$data,"website_id=$this->website_id and product_id = '".$this->product_id."'");
            $product = new ProductModel($this->product_id,$this->website_id);
            $all_html_id = $product->get_html_uid();
            //update the html on the disk
            if (!empty($GLOBALS['env'])) {
                $hresult = writeHtml($all_html_id,$html_content,'/data/html/'.$GLOBALS['env'].'/');
            } else {

                $hresult = writeHtml($all_html_id,$html_content);
            }
            if($result !== false && $hresult) {
                return $all_html_id;
            }
            return true;

        } else {
            return false;
        }
    }

    /**
     *
     * update prodcut's details 
     */
    public function update_details() {
        
    }

   /**
     * validate the product's url
     * @param string
     * @return bool return true if valid ,or false;
     */
    public function validate_url($url) {
        $url = filter_var($url,FILTER_VALIDATE_URL);
        if (empty($this->valid_url)) {
           $url = preg_match($this->valid_url,$url);
        }
        if ($url === false) {
           throw new \ErrorException("Invalid URL $url");
        }
        return true;
    }
}
