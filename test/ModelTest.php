<?php
namespace UnitTest;
include_once("./ExtTestCase.php");
use UnitTest\ExtTestCase;
use Extension\Model;
use Model\ProductModel;
use Extension\DBDriver;

class ModelTest extends ExtTestCase{
    private $website_id;
    private $database;
    private $product_details;
    private $product_id;
    private $model;
    public function setUp() {
        //Prepare the data dependency
        $product_details=file_get_contents('datas/product.php');
        $product_details = unserialize($product_details);
        $this->product_id= array_keys($product_details)[1];
        $this->website_id = 1;
        $this->product_details = $product_details;
        $this->model = new Model();
        $this->model->set('name','bianjianhuang');
    }

    public function testSuperiorModelConstructSuccessful() {
        $model = new Model();
        $this->isInstanceOf(Model::class);
    }

    public function testInferiorModelInitSuccessful() {
        $model = new ProductModel($this->product_id, $this->website_id);
        $this->isInstanceof(ProductModel::class);
    }

    public function testMemberSetedWhenSetMethodCalledSuccessfully(){
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

    public function testMemberGetPropertyForGetMethod() {
        $member =$this->model->get('name');
        $this->assertNotEmpty($member);
        $this->assertEquals($member,'bianjianhuang');
    }

    public function testDBDriverConstructedAfterModelConstructSuccessful(){
       $DBDriver = $this->getProperty($this->model,'DBDriver');
       $this->isInstanceOf($DBDriver,DBDriver::class);
       $this->isInstanceOf($DBDriver,DBExtension::class);
    }

    public function testTableMapGeneratedAfter__mapping_table(){
       $model = new ProductModel($this->product_id,$this->website_id);
       $this->call($model,'__mapping_table');
       $table = $this->getProperty($model,'mapping_table');
       $this->assertNotEmpty($table);
       $this->assertEquals($table,'product');
    }
    public function testFieldsMapGenerateAfter__mapping_fields() {
       $model = new ProductModel($this->product_id,$this->website_id);
       $this->call($model,'__mapping_fields');
       $fields = $this->getProperty($model,'mapping_fields');
       $this->assertInternalType('array',$fields);
       $this->assertNotEmpty($fields);
    }
}
