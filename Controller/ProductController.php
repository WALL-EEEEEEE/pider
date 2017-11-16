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
        Api::proxy_wrapper(function() use ($url,&$html_content){
            $html_content = \requests::get($url);
        });
        $max_try = 3;
        $time_try = 1;
        while(empty($html_content) && $time_try < $max_try) {
            Api::proxy_wrapper(function() use ($url, &$html_content) {
                $html_content = \requests::get($url);
            });
            printf("Failed to get the html content, retry ".$time_try."/".$max_try."\n");
            $time_try++;
        }

        if (!empty($html_content)) {
            $data['uid'] = spawn_guid();
            $data['ctime'] =  date("Y-m-d h:i:s");
            $data['url'] = $url;
            $data['sku_id'] = $this->product_id ;
            $data['website_id'] = $this->website_id;
            $data['runtime'] = date("Y-m-d h:i:s");
            Api::proxy_wrapper(function() use($url,&$header) {
                $header = http::get_http_header($url);
            });
            $max_try = 3;
            $time_try = 1;
            while(!$header && $time_try < $max_try) {
                Api::proxy_wrapper(function() use ($url, &$html_content) {
                    $header = http::get_http_header($url);
                });
                printf("Failed to get the html content, retry ".$time_try."/".$max_try."\n");
                $time_try++;
            }
            if ($header) {
                $data['modify_date'] = @$header['last_modfied'];
                $data['content_length'] = @$header['content_length'];
                $data['etag'] = @$header['etag'];
            } else {
                printf("Failed to get header for url:%s\n",$url);
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
            printf("Failed to get html content for url:%s\n",$url);
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
        Api::proxy_wrapper(function() use ($url,&$html_content) {
            $html_content = \requests::get($url);
        });

        $max_try = 10;
        $try_time = 1;
        while(empty($html_content) && $try_time < $max_try ) {
            Api::proxy_wrapper(function() use ($url,&$html_content) {
                $html_content = \requests::get($url);
            });
            printf("Failed to get the html content, retry ".$time_try."/".$max_try.'\n');
            $time_try++;
        }
        if (!empty($html_content)) {
            Api::proxy_wrapper(function() use($url,&$header) {
                $header = http::get_http_header($url);
            });
            $max_try = 10;
            $time_try = 1;
            while(!$header && $time_try < $max_try) {
                Api::proxy_wrapper(function() use ($url, &$html_content) {
                    $header = http::get_http_header($url);
                });
                printf("Failed to get the html content, retry ".$time_try."/".$max_try.'\n');
                $time_try++;
            }
            if ($header) {
                $data['modify_date'] = @$header['last_modfied'];
                $data['content_length'] = @$header['content_length'];
                $data['etag'] = @$header['etag'];
            } else {
                printf("Failed to get header for url:%s \n",$url);
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
            printf("Failed to get html content for url:%s\n",$url);
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
