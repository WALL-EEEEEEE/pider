<?php
namespace Model;
use Extension\Model;

class WebsiteModel  extends Model {
   private $website_id;

    /**
     * 获取网站的产品url列表
     * @return array|bool|mixed|void
     */
   public function get_urls($filter = array()){
    $table_name = "wine_url";
    $where =" where ";
       if (!empty($filter) && !is_string($filter) && is_array($filter)) {
           printf("%s\n","Argument Error: filter must be a string or array!");
           return false;
       }

       if (is_string($filter)) {
           $where.= $filter." and ";
       }
       if (is_array($filter)) {
           foreach($filter as $item) {
               $where.=$item." and ";
           }
       }
    $where.="website_id=".$this->website_id;
    $sql = "select product_url, out_product_id,update_time from ${table_name}".$where;
    $urls = \db::get_all('select product_url,out_product_id,update_time from '.$table_name.' where website_id = '.$this->website_id);
    \db::clear_link();
    return $urls;
   }

   /**
    * 获取网站的html的产品信息
    * @param  mixed  $filter  根据数据库where条件过滤信息
    */
   public function get_html($filter = array(),$field_limit=array(),$limit="") {
       $where =" where ";
       $fields = "*";
       if (!empty($filter) && !is_string($filter) && !is_array($filter)) {
           printf("%s\n","Argument Error: filter must be a string or array!");
           return false;
       }

       if (!empty($field_limit) && !is_string($field_limit) && !is_array($field_limit)) {
           printf("%s\n","Argument Error: field_limit must be a string or array!");
           return false;
       }

       if(!empty($limit) && !is_numeric($limit)) {
           printf("%s\n","Argument Error: limit must be numberic!");
           return false;
       }

       if (!empty($filter)&&is_string($filter)) {
           $where.= $filter." and ";
       }
       if (!empty($filter)&&is_array($filter)) {
           foreach($filter as $item) {
               $where.=$item." and ";
           }
       }

       if (!empty($field_limit) && is_string($field_limit)) {
          $fields = $field_limit;
       }
       if (!empty($field_limit) && is_array($field_limit)) {
           $fields = "";
          foreach($field_limit as $field) {
              $fields .= $field.",";
          }
          $fields = trim($fields,',');
       }
       $where.="website_id=".$this->website_id;
       $sql = "select $fields from all_html".$where;
       if (!empty($limit)) {
          $sql.=" limit ".$limit;
       }
       $htmls = \db::get_all($sql);
       if (!empty($htmls)) {
           return $htmls;
       }
       return false;
   }

    /*
     * Route method to ProductModel method , do some batches operation for Product  
     * 
     * */
    public function __call($method,$args) {
        $product = new ProductModel();
        if (!method_exists($this,$method) && method_exists($product,$method) && is_array($args)){
            foreach($args as $arg) {
                $product->$method($arg);
            }
        }
    }
}
