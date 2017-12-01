<?php
namespace Module\Data;

class Pore {

   /**
    * @attribute  string $pore_uid  a unique id representing Pore
    */
   private $pore_uid = '';

   /**
    * @attribute  array  Data self-clean based
    *
    */
   private $self_datas = '';

   public function __construct(string $poreId = '', $self_datas = '') {
       if (empty($poreId)) {
           $poreId  = $this->__id();
       } 
       if (!empty($data)) {
           $this->self_datas  = $self_datas;
       }
       $pore_uid = $poreId;
  }

   /**
    * @method getPoreId()
    * @return Current PoreId is returned
    * Get current PoreId
    */
   public function getPoreId():string {
       return $pore_uid;
   }

   /**
    * @method __id()
    * @return string  a unique indentification pore id returned
    *
    * Spawn a unique identification id for pore
    *
    */
   private function __id() {
       if (function_exists('com_create_guid') == true) {
           return trim(com_create_guid(),"{}");
       }
       return strtolower(sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
   }

}
