<?php
namespace UnitTest\Model;
include_once("../ExtTestCase.php");
use UnitTest\ExtTestCase;
use Extension\Model;
use Extension\DBExtension;
use Model\ProductModel;
use Extension\DBDriver;

/**
 * Unit Test for base Model test
 */
class ModelTest extends ExtTestCase{
    protected $website_id;
    protected $database;
    protected $product_details;
    protected $product_id;
    protected $model;
    public function setUp() {
        //Prepare the data dependency
        DBExtension::switch_db('jhbian_spider');
        $product_details=file_get_contents('../datas/product.php');
        $product_details = unserialize($product_details);
        $this->product_id= array_keys($product_details)[1];
        $this->website_id = 1;
        $this->product_details = $product_details[$this->product_id];
        $this->model = new Model();
    }

    /**
     * model class can be initialized successfully
     */
    public function testModelConstructSuccessful() {
        $this->isInstanceOf($this->model,Model::class);
    }

    /**
     *
     * member can be set through set() method
     */
    public function testMemberSetedWhenSetMethodCalledSuccessfully(){
        $this->model->set('name','bianjianhuang');
        $members = $this->getProperty($this->model,'members');
        $this->assertInternalType('array',$members);
        $this->assertNotEmpty($members);
        $this->assertGreaterThan(0,count($members));
        $this->assertEquals($members['name']['value'],'bianjianhuang');
        $this->assertEquals($members['name']['name'],'name');
        $this->assertEquals($members['name']['property'],Model::OPTIONAL);
    }
    public function testMemberPropertySetForSetMethod(){
        $this->model->set('name','bianjianhuang',Model::REQUIRED,null,true);
        $members = $this->getProperty($this->model,'members');
        $this->assertEquals($members['name']['property'],Model::REQUIRED);
    }

    /***
     *
     * get() method can get member which be set by either set() method or __set() magic method
     */
    public function testMemberGetPropertyForGetMethod() {
        $this->model->set('name','bianjianhuang');
        $this->model->title = 'Grape Wine';
        $name =$this->model->get('name');
        $title = $this->model->get('title');
        $this->assertNotEmpty($name);
        $this->assertEquals($name,'bianjianhuang');
        $this->assertNotEmpty($title);
        $this->assertEquals($title,'Grape Wine');
    }

    /**
     * get()  method retrun empty string when the member don't exist
     */
    public function testEmptyGetMethodForPropertyNoExist(){
        $member =$this->model->get('name');
        $this->assertEmpty($member);
    }
    /**
     *
     * member can be set by __set() magic function 
     */
    public function testMemberPropertySetFor__setMethod(){
        $this->model->title = 'Grape wine';
        $members = $this->getProperty($this->model,'members');
        $this->assertNotEmpty($members);
        $expected_member = array('name'=>'title','value'=>'Grape wine','property'=>0);
        $this->assertContains($expected_member,$members);
    }
    /**
     *
     * member setted by __set() magic function  and set() function can be get by __get() magic method
     */
    public function testMemberGetMemberPropertyGetFor__getMethod() {
        $this->model->title = "Grape Wine";
        $this->model->set('product_id',30);
        $this->assertNotEmpty($this->model->title);
        $this->assertNotEmpty($this->model->product_id);
        $this->assertEquals($this->model->title,'Grape Wine');
        $this->assertEquals($this->model->product_id,30);
    }
    /**
     * When Model Object is constructed successfully, A DBDriver Object property will be generated. 
     */
    public function testDBDriverConstructedAfterModelConstructSuccessful(){
       $DBDriver = $this->getProperty($this->model,'DBDriver');
       $this->isInstanceOf($DBDriver,DBDriver::class);
       $this->isInstanceOf($DBDriver,DBExtension::class);
    }

    /**
     * if __mapping_table is called by Model object, $mapping_table will be empty
     */
    public function testTableMapEmptyAfterModel__mapping_table(){
       $this->call($this->model,'__mapping_table');
       $table = $this->getProperty($this->model,'mapping_table');
       $this->assertEmpty($table);
    }
    /**
     *
     * if members  and mapping_fileds are both empty, mapping_fields will still  be empty after  __mapping_fields() called
     */
    public function test__mapping_fields() {
       $this->call($this->model,'__mapping_fields');
       $fields = $this->getProperty($this->model,'mapping_fields');
       $this->assertInternalType('array',$fields);
       $this->assertEmpty($fields);
    }

    /**
     * if a member is set by set() method in Model, the member will be collected into the mapping_fields
     */
    public function testMembersSetBySetCollectedby__mapping_fields() {
        $this->model->set('name','bianjianhuang');
        $this->call($this->model,'__mapping_fields');
        $fields = $this->getProperty($this->model,'mapping_fields');
        $excepted_fields= array('name'=>'name');
        $this->assertInternalType('array',$fields);
        $this->assertNotEmpty($fields);
        $this->assertEquals($excepted_fields,$fields);
    }

    /**
     * if a member  is set by __set() magic method in Model, the member will be collected into the mapping_fields 
     */
    public function testMembersSetBy__setCollectedby__mapping_fields() {
        $this->model->name = 'jhohans';
        $this->call($this->model,'__mapping_fields');
        $fields = $this->getProperty($this->model,'mapping_fields');
        $excepted_fields = array('name'=>'name');
        $this->assertInternalType('array',$fields);
        $this->assertNotEmpty($fields);
        $this->assertEquals($excepted_fields,$fields);
    }

    /**
     * fromArray() return ErrorException when the arg passed isn't an array
     */
    public function testfromArrayThrowErrorExceptionArgsIsNotArray() {
        $args = 'test';
        $fromArray = $this->getInAccessibleMethod($this->model,'fromArray');
        $this->assertException($fromArray,$args,\ErrorException::class,null,'Argument Error: The 1st Argument must be an array');
    }

    /**
     *fromArray() generates members from array and return Model Object,when called in Model class
     */

    public function testfromArrayCalledSuccessfully() {
        $return = $this->model->fromArray($this->product_details);
        $members = $this->getProperty($this->model,'members');
        $this->assertContains($this->product_details['name'],$members['name']);
        $this->assertContains($this->product_details['id'],$members['id']);
        $this->isInstanceOf(Model::class);
    }

    public function testfromArrayWith__setAndSet(){
        $return = $this->model->fromArray($this->product_details);
        $this->model->set('name','bianjianhuang');
        $members = $this->getProperty($this->model,'members');
        $expected_name = $this->product_details['name'];
        $actual_names = $members['name'];
        $this->assertNotEquals($expected_name,$actual_names['value']);
        $expected_id = $this->product_details['id'];
        $actual_id = $members['id'];
        $this->assertEquals($expected_id,$actual_id['value']);
        $this->model->id = 3;
        $extected_id = $this->product_details['id'];
        $members = $this->getProperty($this->model,'members');
        $actual_id = $members['id'];
        $this->assertNotEquals($expected_id,$actual_id['value']);
        $this->isInstanceOf(Model::class);
    }
    public function testUpdateMethodSuccessfully() {
        $model = $this->model->fromArray($this->product_details);
        $model->linkTable('wine_info');
        $model->linkFields(array(
            'id'=>'out_product_id',
            'name'=>'name_ch',
            'pro_price'=>'current_price',
            'url'=>'product_url',
            'price'=>'market_price',
        ));
        var_dump($model->update());
        $this->assertNotFalse($model->update());
    }
}
