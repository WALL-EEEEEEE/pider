<?php
use module;

class website{
   private static $db_config = array();
   private $website_id;

   public function  __construct($product_id, $db_config) {
      $this->product_id = $product_id;
      $this->db_config = $db_config;
   }

    /**
     * 获取网站的产品url列表
     * @return array|bool|mixed|void
     */
   public function get_urls(){
    $table_name = "wine_url";
    db::set_connect("spider",$this->db_config);
    $urls = db::get_all('select product_url,out_product_id,update_time from '.$table_name.' where website_id = '.$this->website_id);
    db::clear_link();
    return $urls;
   }
}


