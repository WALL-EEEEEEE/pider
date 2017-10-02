<?php
namespace UnitTest\Model;
include_once('./ModelTest.php');
include_once('./Classes/ExampleModel.php');
use Extension\Model;
use Extension\DBDriver;
/**
 * Unit Test for inferior Model class,without default property
 */
class ExampleModelTest extends ModelTest{
    public function setUp() {
        $product_details=file_get_contents('../datas/product.php');
        $product_details = unserialize($product_details);
        $this->product_id= array_keys($product_details)[1];
        $this->website_id = 1;
        $this->product_details = $product_details;
        $this->model = new ExampleModel();
    }
    /**
     * if __mapping_table is called by a object extending Model with xxModel class name , $mapping_table will be named as xx(for example:
     * class ExampleModel =>table example ). 
     */
    public function testTableMapEmptyAfterModel__mapping_table(){
       $this->call($this->model,'__mapping_table');
       $table = $this->getProperty($this->model,'mapping_table');
       $this->assertEquals($table,'example');
    }
 
}
