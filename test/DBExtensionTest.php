<?php
namespace UnitTest;
include_once('./ExtTestCase.php');
use Extension\DBExtension;

class DBExtensionTest extends ExtTestCase{

    /**Assure DBExtension's tables function return a array of database collections **/
    public function testReturnNoEmptyArray__tablesCalledSuccessfullyWithTablesExistence() {
        DBExtension::switch_db('phpspider');
        $tables = $this->call('Extension\DBExtension','__tables');
        $this->assertInternalType('array',$tables);
        $this->assertNotEmpty($tables);
    }

    public function testReturnNoEmptyArray__fieldsCalledSuccessfullyWithFieldsExsitence() {
        DBExtension::switch_db('phpspider');
        $this->setProperty('Extension\DBExtension','table','all_html',true);
        $fields = $this->call('Extension\DBExtension','__fields',array(),array(),true);
        $this->assertInternalType('array',$fields);
        $this->assertNotEmpty($fields);
    }

    public function testFieldCachedSet__fieldsCalledSuccessfullyWithFieldsExistence() {
        DBExtension::switch_db('phpspider');
        $this->setProperty('Extension\DBExtension','table','all_html',true);
        $this->call('Extension\DBExtension','__fields',array(),array(),true);
        $cached_fields = $this->getProperty('Extension\DBExtension','cached_fields','',true);
        $this->assertInternalType('array',$cached_fields);
        $this->assertNotEmpty($cached_fields);
    }

    public function testTablesCachedSet__tablesCalledSuccessfullyWithTablesExistence() {
        DBExtension::switch_db('phpspider');
        $this->call('Extension\DBExtension','__tables',array(),array(),true);
        $cached_tables = $this->getProperty('Extension\DBExtension','cached_tables','',true);
        $this->assertInternalType('array',$cached_tables);
        $this->assertNotEmpty($cached_tables);
    }

    public function testErrorExceptionThrown__fieldsCalledFailedWithoutTableSpecified(){
        DBExtension::switch_db('phpspider');
        $this->setProperty('Extension\DBExtension','table','',true);
        $__fields = $this->getInAccessibleMethod('Extension\DBExtension','__fields',array(),true);
        $this->assertException($__fields,'ErrorException',NULL,'Error: No Table Name Specified!');
    }

    /**
     * if table not exists in database , throw a ErrorException 
     *
     */
    public function testErrorExceptionThrow__fieldsCalledFailedWithTableNoExist() {
        DBExtension::switch_db('phpspider');
        //set a unexsitence tablename
        $this->setProperty('Extension\DBExtension','table','random',true);
        $__fields = $this->getInAccessibleMethod('Extension\DBExtension','__fields',array(),true);
        $this->assertException($__fields,'ErrorException',NULL,'Error: No Such Table Name In Database!');
    }

    /**
     * if tables fields is empty except the auto_increment field in database ,__fileds return failed
     */
    public function testFalseReturned__fieldsCalledSuccessfullyWithFieldsNoExist() {
        DBExtension::switch_db('jhbian_spider');
        //set a valid tablename without any fields inside
        $this->setProperty('Extension\DBExtension','table','test',true);
        $__fields  = $this->call('Extension\DBExtension','__fields',array(),array(),true);
        $this->assertFalse($__fields);
    }
}
