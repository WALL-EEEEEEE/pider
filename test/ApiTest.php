<?php
namespace UtilTest;
include_once("ExtTestCase.php");
use UnitTest\ExtTestCase;
use Util\Api;

class ApiTest extends ExtTestCase{

    public function testgetIps(){
        var_dump(Api::getIp());
    }
}
