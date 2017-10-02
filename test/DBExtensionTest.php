<?php
namespace UtilTest;
include_once('./ExtTestCase.php');
use Extension\DBExtension;

class DBExtensionTest extends \ExtTestCase{

    /**Assure DBExtension's tables function return a array of database collections **/
    public function testReturnNoEmptyArray__tablesCalledSuccessfullyWithTablesExistence() {
        DBExtension::switch_db('phpspider');
        $tables = $this->call('Extension\DBExtension','__tables');
        $this->assertInternalType('array',$tables);
        $this->assertNotEmpty($tables);
    }

    public function testReturnNoEmptyArray__fieldsCalledSuccessfullyWithFieldsExsitence() {
        DBExtension::switch_db('phpspider');
        $this->setProperty('Extension\DBExtension','table','all_html');
        $fields = $this->call('Extension\DBExtension','__fields');
        $this->assertInternalType('array',$fields);
        $this->assertNotEmpty($fields);
    }

    public function testFieldCachedSet__fieldsCalledSuccessfullyWithFieldsExistence() {
        DBExtension::switch_db('phpspider');
        $this->setProperty('Extension\DBExtension','table','all_html');
        $this->call('Extension\DBExtension','__fields');
        $cached_fields = $this->getProperty('Extension\DBExtension','cached_fields');
        $this->assertInternalType('array',$cached_fields);
        $this->assertNotEmpty($cached_fields);
    }

    public function testTablesCachedSet__tablesCalledSuccessfullyWithTablesExistence() {
        DBExtension::switch_db('phpspider');
        $this->call('Extension\DBExtension','__tables');
        $cached_tables = $this->getProperty('Extension\DBExtension','cached_tables');
        $this->assertInternalType('array',$cached_tables);
        $this->assertNotEmpty($cached_tables);
    }

    public function testErrorExceptionThrown__fieldsCalledFailedWithoutTableSpecified(){
        DBExtension::switch_db('phpspider');
        $this->setProperty('Extension\DBExtension','table','');
        $__fields = $this->getInAccessibleMethod('Extension\DBExtension','__fields');
        $this->assertException($__fields,'ErrorException',NULL,'Error: No Table Name Specified!');
    }

    public function testErrorExceptionThrow__fieldsCalledFailedWithTableNoExist() {
        DBExtension::switch_db('phpspider');
        //set a unexsitence tablename
        $this->setProperty('Extension\DBExtension','table','random');
        $__fields = $this->getInAccessibleMethod('Extension\DBExtension','__fields');
        $this->assertException($__fields,'ErrorException',NULL,'Error: No Such Table Name In Database!');
    }

    public function testFalseReturned__fieldsCalledSuccessfullyWithFieldsNoExist() {
        DBExtension::switch_db('jhbian_spider');
        //set a valid tablename without any fields inside
        $this->setProperty('Extension\DBExtension','table','test');
        $__fields  = $this->call('Extension\DBExtension','__fields');
        $this->assertFalse($__fields);
    }

}
