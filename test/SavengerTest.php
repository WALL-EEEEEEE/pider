<?php
namespace UnitTest;
include_once('./ExtTestCase.php');
use  Unit\Savenger;
use  UtilTest\ExtTestCase;

/**
 *UnitTest for Savenger class
 */
class SavengerTest  extends ExtTestCase {

    private $savenger;
    /**
     * Prepare for some data
     */
    public function SetUp(){
        $this->savenger = new Savenger();
    }
    public function testSavengerSuccessfully() {
    }

}



