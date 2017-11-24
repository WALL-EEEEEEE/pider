<?php
namespace Model;

use Extension\DBExtension;
use Extension\Model;
/**
 * Class UrltagModel
 *  A model class to manipulate tags of url.
 * @package model
 *
 */
class UrltagModel extends Model {

   /**
    * @field
    */
   private $website_id;

   /**
     *  Get all url tags's categories
     */
   public static function get_types() {
      $sql = "select * from url_tag_type";
      $types = DBExtension::get_all($sql);
      if ($types === false) {
          return false;
      }
      if (is_array($types)) {
          $ttypes = array();
          foreach($types as $types) {
              $ttypes[$types['uid']] = $types['type_name'];
          }
          $types = $ttypes;
      }
      return $types;
   }

    /**
     * Prune the tags
     */
   public function prune($name) {
       $sql = "delete from url_tag  
                   where  uid in (
                    select * from (
                       select url_tag.uid from url_tag,all_html where url_tag.ah_id = all_html.uid and tag_desc like '%".$name."'"." and website_id =".$this->website_id." ) as m)";
       $result = DBExtension::query($sql);
       if (!$result) {
           return false;
       }
       return true;
   }

   /**
    *
    * Prune the tags by product_id
    * @param $product_id mixed, product to being removed
    */ 
   public function prune_by_product_id($product_id){
       if (empty($product_id)) {
           return false;
       }
       $sql = '';
       if (is_array($product_id)) {
           $product_id = array_quote($product_id);
           $ids = implode(',',$product_id);
           $sql = "delete from url_tag  
               where  uid in (
                   select * from (
                       select url_tag.uid from url_tag,all_html where url_tag.ah_id = all_html.uid and sku_id in (".$ids.")"." and website_id =".$this->website_id." ) as m)";

       } else {
               $sql = "delete from url_tag  
               where  uid in (
                   select * from (
                       select url_tag.uid from url_tag,all_html where url_tag.ah_id = all_html.uid and sku_id= '".$product_id."'"." and website_id =".$this->website_id." ) as m)";
       }
       $result = DBExtension::query($sql);
       if (!$result) {
           return false;
       }
       return true;
   }

   public function prune_by_website_id($website_id='') {
       if (empty($website_id) && empty($this->website_id)) {
           throw new \ErrorException('Error: Website id is not specified');
       }
       if (empty($website_id)) {
           $website_id = $this->website_id;
       }
       $sql = "delete from url_tag  
                   where  uid in (
                    select * from (
                       select url_tag.uid from url_tag,all_html where url_tag.ah_id = all_html.uid "." and website_id =".$website_id.") as m)";  
       $result = DBExtension::query($sql);
       if (!$result) {
           return false;
       }
       return true;
   }

   public function get_by_website_id($website_id='') {

       if (empty($website_id) && empty($this->website_id)) {
           throw new \ErrorException('Error: Website id is not specified');
       }
       if (empty($website_id)) {
           $website_id = $this->website_id;
       }
       $sql = "select tag_desc as tag_name,ah_id as all_html_id from url_tag,all_html where url_tag.ah_id = all_html.uid and website_id = ".$website_id;  
       $result = DBExtension::query($sql);
       if (!$result) {
           return false;
       }
       return $result;
   }

}
