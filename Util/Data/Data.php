<?php
namespace Data;
use Rule;

class Data {
   private  $rule = null;
   private  $data = null;
   public function __construct($data,Rule $rule) {
       $this->data = $data;
       $this->rule = $rule;
   }
}
