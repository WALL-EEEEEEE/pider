<?php
namespace Model ;

use Extension\Model;

class ProductModel extends Model {
    private $product_id = -1;
    private $website_id = -1;

    //define essential properties for product
    private $url = array(
        'value'=>'',
        'required'=>true,
    );
   private $price = array(
        'value'=>'',
        'required'=>true,
    );

    public function __construct($product_id,$website_id)
    {
        parent::__construct();
        $this->product_id = empty($product_id) ? -1:$product_id;
        $this->website_id = empty($product_id) ? -1:$website_id;
    }

    /**
     *
     *获取产品链接数据库中的唯一id
     * @param $product_id
     * @return bool
     */
    public function get_uid() {
        $table_name = "all_html";
        if ($this->product_id < 0 || $this->website_id < 0) {
          return false;
        }
        $uid = \db::get_one('select uid from '.$table_name.' where sku_id='.$this->product_id.' and website_id='.$GLOBALS['website']['id']);
        if (empty($uid)) {
            return false;
        }
        return $uid['uid'];
    }

    /**
     * 获取当前URL的更新状态
     * @param $uid
     * @return bool|int
     */
    public static function get_runtime_status($uid){
        $table_name = "all_html";
        $status = \db::get_one('select flag from '.$table_name.' where uid="'.$uid.'"');
        if (empty($status) && empty($status["flag"])) {
            return false;
        }
        return intval($status["flag"]);
    }

    public static function get_last_header($uid) {
        $table_name = "all_html";
        $header = \db::get_one('select modify_date as last_modified,etag,content_length,runtime as date from '.$table_name.' where uid='.$uid);
        if (empty($header)) {
            return false;
        }
        return $header;
    }

    public static function get_html($uid){
        $table_name = "all_html";
        $status = \db::get_one('select html from '.$table_name.' where uid="'.$uid.'"');
        if (empty($status) && empty($status["html"])) {
            return false;
        }
        return $status['html'];
    }

    /**
     * Get get html's uid from  all_html table by product_id
     * @param $product_id
     */
    public function get_html_uid(){
        $table_name = "all_html";
        $status = \db::get_one('select uid from '.$table_name.' where sku_id="'.$this->product_id.'" and website_id = '.$this->website_id);
        if (empty($status) && empty($status["uid"])) {
            return false;
        }
        return $status['uid'];
    }


    /**
     * Add a new html
     */
    public function add_html($html_meta) {
        $table_name = "all_html";
        $status = \db::insert($table_name,$html_meta);
        return $status;
    }

    /**
     * update html
     */
    public function update_html($html_meta) {
       $table_name = "all_html";
       $status = \db::update($table_name,$html_meta);
       return $status;
    }
}



