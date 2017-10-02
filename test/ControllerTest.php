<?php
namespace UnitTest;
include_once("./ExtTestCase.php");
use UnitTest\ExtTestCase;
use Extension\Controller;
use Controller\ProductController;
use Controller\ProductModel;

class ControllerTest extends ExtTestCase{
    private $website_id;
    private $database;
    private $product_details;
    private $product_id;
    public function setUp() {
        //Prepare the data dependency
        $product_details=file_get_contents('datas/product.php');
        $product_details = unserialize($product_details);
        $this->product_id= array_keys($product_details)[1];
        $this->website_id = 1;
        $this->product_details = $product_details;
    }
    public function testControllerInitSuccessfulThroughNew() {
        $controller = new ProductController($this->product_id,$this->website_id);
        $this->isInstanceOf(ProductController::class);
    }
    public function testInferiorModelInitSuccessfulThroughControllerModelMethod(){
        $controller = new ProductController($this->product_id,$this->website_id);
        $model = $controller::model($this->product_id,$this->website_id);
        $this->isInstanceOf(ProductModel::class);
    }
    public function testSuperiorModelInitSuccessfulThroughControllerModelMethod(){
        $controller = new Controller();
        $model = $controller::model();
        $this->isInstanceOf(Model::class);
    }

}
