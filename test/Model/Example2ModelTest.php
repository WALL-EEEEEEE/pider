<?php
namespace UnitTest\Model;
include_once('./ModelTest.php');
include_once('./Classes/ProductModel.php');
use Model\ProductModel;
/**
 *
 *  Unit Test for inferior Model class,with default property
 *
 */
class Example2ModelTest extends ModelTest {
    public function setUp() {
        $product_details=file_get_contents('../datas/product.php');
        $product_details = unserialize($product_details);
        $this->product_id= array_keys($product_details)[1];
        $this->website_id = 1;
        $this->product_details = $product_details;
        $this->model = new ProductModel($this->product_id,$this->website_id);
    }
    /**
     * if __mapping_table is called by a object extending Model with xxModel class name , $mapping_table will be named as xx(for example:
     * class ExampleModel =>table example ). 
     */
    public function testTableMapEmptyAfterModel__mapping_table(){
       $this->call($this->model,'__mapping_table');
       $table = $this->getProperty($this->model,'mapping_table');
       $this->assertEquals($table,'product');
    }
    /**
     *
     * if mapping_fileds are empty, but default member property exist, mapping_fields will be an key-value array generated from default member property after __mapping_fields() called
     */
    public function test__mapping_fields() {
       $this->call($this->model,'__mapping_fields');
       $fields = $this->getProperty($this->model,'mapping_fields');
       $this->assertInternalType('array',$fields);
       $this->assertNotEmpty($fields);
       $this->assertEquals(array('price'=>'price','website_id'=>'website_id','product_id'=>'product_id','url'=>'url'),$fields);
    }

    /**
     * if a member is set by set() method in Model, the member will be collected with default member property into the mapping_fields
     */
    public function testMembersSetBySetCollectedby__mapping_fields() {
        $this->model->set('name','bianjianhuang');
        $this->call($this->model,'__mapping_fields');
        $fields = $this->getProperty($this->model,'mapping_fields');
        $expected_set_fields= array('name'=>'name');
        $expected_default_fields  = array('price'=>'price','website_id'=>'website_id','product_id'=>'product_id','url'=>'url');
        $this->assertInternalType('array',$fields);
        $this->assertNotEmpty($fields);
        $this->assertArraySubset($expected_set_fields,$fields);
        $this->assertArraySubset($expected_default_fields,$fields);
    }

    /**
     * if a member  is set by __set() magic method in Model, the member will be collected with default member property into the mapping_fields 
     */
    public function testMembersSetBy__setCollectedby__mapping_fields() {
        $this->model->name = 'jhohans';
        $this->call($this->model,'__mapping_fields');
        $fields = $this->getProperty($this->model,'mapping_fields');
        $expected__set_fields = array('name'=>'name');
        $expected_default_fields = array('price'=>'price','website_id'=>'website_id','product_id'=>'product_id','url'=>'url');
        $this->assertInternalType('array',$fields);
        $this->assertNotEmpty($fields);
        $this->assertArraySubset($expected__set_fields,$fields);
        $this->assertArraySubset($expected_default_fields,$fields);
    }



}
