<?php
namespace module\ecommerce\controller;

use ext\controller;

class product  extends controller {
    private $website_id = -1;
    private $product_id = -1;

    public function __construct($product_id,$website_id)
    {
        $this->website_id = $website_id;
        $this->product_id = $product_id;
    }

    //add html for product
    public function add_html($url,$encoding="UTF-8") {
        $data = array();
        \requests::$input_encoding = $encoding;
        \requests::$output_encoding = $encoding;
        $html_content = \requests::get($url);
        if (!empty($html_content)) {
            $data['uid'] = spawn_guid();
            $data['ctime'] =  date("Y-m-d");
            $data['url'] = $url;
            $data['sku_id'] = $this->product_id ;
            $data['website_id'] = $this->website_id;
            $data['runtime'] = date("Y-m-d");
            $header = http::get_http_header($url);
            if ($header) {
                $data['modify_date'] = @$header['last_modfied'];
                $data['content_length'] = @$header['content_length'];
                $data['etag'] = @$header['etag'];

            }
            //update the data in database
            $result = \db::insert('all_html',$data);
            //update the html on the disk
            $hresult = writeHtml($this->product_id,$html_content);
            if($result === false || $hresult) {
                return false;
            }
            return true;


        } else {
            return false;
        }

    }

    //update html for product
    public function update_html($url, $encoding= "UTF-8"){
        $data = array();
        \requests::$input_encoding = $encoding;
        \requests::$output_encoding = $encoding;
        $html_content = \requests::get($url);
        if (!empty($html_content)) {
            $data['url'] = $url;
            $data['runtime'] = date("Y-m-d");
            $header = http::get_http_header($url);
            if ($header) {
                $data['modify_date'] = @$header['last_modfied'];
                $data['content_length'] = @$header['content_length'];
                $data['etag'] = @$header['etag'];
            }

            //update the data in database
            $result = \db::update('all_html',$data,"website_id=$this->website_id and product_id = '".$this->product_id."'");
            //update the html on the disk
            $hresult = writeHtml($this->product_id,$html_content);
            if($result === false || !$hresult) {
                return false;
            }
            return true;

        } else {
            return false;
        }
    }
}
