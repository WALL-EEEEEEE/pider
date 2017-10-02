<?php
namespace UnitTest;

use PHPUnit\Framework\TestCase;
use Util\StructHtml\MultiTabs;

/**
 * Unit Test for Multitabs class
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/9/7
 * Time: 12:12
 */

class MultitabsTest extends  TestCase{

    /**
     *  Test for  multitabs::get_all_elements return a empty array with a correct argument
     */
   public function testGetAllElementsReturnNotEmptyArray() {

       $flash_url = "http://a.yesmywine.com/flash/index";
       $flash_subtabs = array('Bourgeoismall');
       $flash_selectors = array(
           $flash_url=>array(
               'index'=> "//div[contains(@class,'section')][1]/ul/li/a/@href",
               'Bourgeoismall'=> array(
                   '//div[contains(@class,"section")][1]/ul/li/a/@href',
                   '//div[contains(@class,"shop")]/a/@href',
               ),
           ),
       );
       //从html中获取限时抢购标签
       $rush_url = "http://www.yesmywine.com/cms/cmsV1/second/index";
       $rush_subtabs = array("qc");
       $rush_selectors = array(
           $rush_url=>"//ul[contains(@class,'prodlist')]/li/div/a/@href",
       );

       printf("%s\n","Getting rush tag info from flash html ...");
       $rush_tag_datas = MultiTabs::get_all_elements($rush_url,$rush_selectors,$rush_subtabs,'slash','http://www.yesmywine.com/cms/cmsV1/second');
       var_dump($rush_tag_datas);
 printf("%s\n","Getting flash tag info from flash html ...");
       $this->assertNotEmpty($rush_tag_datas);
       $flash_tag_datas = MultiTabs::get_all_elements($flash_url,$flash_selectors,$flash_subtabs,'slash','http://a.yesmywine.com/flash');
       $this->assertNotEmpty($flash_tag_datas);
       var_dump($flash_tag_datas);
   }

}



