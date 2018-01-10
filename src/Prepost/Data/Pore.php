<?php
namespace Module\Data;

abstract class Pore {

   /**
    * @attribute  string $pore_uid  a unique id representing Pore
    */
   private $pore_uid;
   /**
    * @attribute  array  Data self-clean based
    */
   public  $self_datas;
   protected $dirty_data;
   private $reactions;
   private $absorbers; 
   private $filters;

   final public function __construct(string $poreId = '', $self_datas = '') {
       if (empty($poreId)) {
           $poreId  = $this->__id();
       } 
       if (!empty($self_data)) {
           $this->self_datas  = $self_datas;
       }
       $this->pore_uid = $poreId;
       $selfFeatures = $this->selfFeatures();
       $this->reactions  = isset($selfFeatures['reaction'])?$selfFeatures['reaction']:[];
       $this->absorbers = isset($selfFeatures['absorber'])?$selfFeatures['absorber']:[];
       $this->filters   = isset($selfFeatures['filter'])?$selfFeatures['filter']:[];
   }

   protected abstract function selfFeatures():array;

   /**
    * @method getPoreId()
    * @return Current PoreId is returned
    * Get current PoreId
    */
   public function getPoreId():string {
       return $pore_uid;
   }

   /**
    *@method 
    *
    */
   public function addReact(Reaction $reaction) {
       $this->reactions[] = $reaction;
   }

   public function addAbsorber(Absorber $absorber) {
       $this->absorbers[] = $absorber;
   }

   public function addFilter(Filter $filter) {
   }

   public function active(array $data) {
       $this->dirty_data = $data;
       if(!empty($this->filters)) {
           while(list(,$filter) = each($this->filters)) {
               $filtered_data = $filter($this->dirty_data,$this);
               if (empty($filtered_data)){
                   return false;
               } else {
                   if (is_array($filtered_data)) {
                       $this->dirty_data =  $filtered_data;
                   }
               }
           }
       }
       if (!empty($this->absorbers)) {
           while(list(,$absorber) = each($this->absorbers)) {
             $this->dirty_data =  $absorber($this->dirty_data,$this);
           }
       }
       if (!empty($this->reactions)) {
           while(list(,$reaction)= each($this->reactions)) {
            $this->dirty_data =  $reaction($this->dirty_data,$this);
           }
       }
       return $this->dirty_data;
   }

   public function __invoke(array $data) {
       return $this->active($data);
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
