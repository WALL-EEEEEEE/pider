<?php
/**Test unit for searchentry class
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/9/11
 * Time: 15:33
 */

namespace test;

require_once(dirname(__FILE__).'/../util/common.php');
require_once(dirname(__FILE__).'/../phpspider/core/requests.php');
require_once(dirname(__FILE__).'/../phpspider/core/selector.php');

use PHPUnit\Framework\TestCase;
use util\structhtml\searchentry;

class SearchentryTest extends  TestCase
{

    /**
     * Ensure non-empty the search result returned  when right parameter is passed
     */
    public function testSearchReturnNoEmpty(){
       $searcher = new searchentry("https://search.jd.com/Search");
       $search_result = $searcher->search('葡萄酒 闪购')->go();
       self::assertNotEmpty($search_result);
    }

    /**
     * Ensure html result returned when selector without giving
     */
    public function testSearchReturnHtmlWithoutSelector(){
        $searcher = new searchentry("https://search.jd.com/Search");
        $search_result = $searcher->search('葡萄酒 闪购')->go();
        $is_html = \is_html($search_result);
        self::assertTrue($is_html);
    }

    /**
     * Ensure an non-empty array returned with selector gived correct
     */
    public function testSearchResturnNoEmptyArrayWithSelector(){
        $searcher = new searchentry("https://search.jd.com/Search");
        $search_result = $searcher->search('葡萄酒 闪购','//div[@id="J_goodsList"]/ul/li/div/div/a/@href')->go();
        self::assertTrue(is_array($search_result));
        self::assertNotEmpty($search_result);
    }

    /**
     *  ErrorException returned if invalid xpath gived while using search method
     */
    public function testSearchReturnErrorExceptionWithInvalidXpath(){
       $searcher = new searchentry("https://search.jd.com/Search");
       $this->setExpectedException('\ErrorException');
       $searcher->search('闪购',"//div()");

    }

    /**
     * ErrorException returned if invalid xpath gived  while using page method
     */

    public function testPageReturnErrorExceptionWithInvalidXpath() {
       $searcher = new searchentry("https://search.jd.com/Search");
       $this->setExpectedException('\ErrorException');
       $searcher->page("//div()");

    }

    /**
     * ErrorException returned if invalid xpath gived while use tabs method
     */
    public function testTabsReturnErrorExceptionWithInvalidXpath() {
       $searcher = new searchentry("https://search.jd.com/Search");
       $this->setExpectedException('\ErrorException');
       $searcher->tabs("//div()");
    }

    /**
     * ErrorException returned if invalid xpath gived while use totalpages method
     */
    public function testTotalpagesReturnErrorExceptionWithInvalidXpath() {
       $searcher = new searchentry("https://search.jd.com/Search");
       $this->setExpectedException('\ErrorException');
       $searcher->totalpages("//div()");
    }

    /**
     * ErrorException returned if invalid xpath gived while use resultbox method
     */
    public function testResultsboxReturnErrorExceptionWithInvalidXpath() {
       $searcher = new searchentry("https://search.jd.com/Search");
       $this->setExpectedException('\ErrorException');
       $searcher->resultsbox("//div()");
    }

    /**
     * Ensure an non-empty array returned with pages feature enabled
     */
    public function testSearchReturnNoEmptyArrayWithPage(){
        $searcher = new searchentry("https://search.jd.com/Search");
        $search_result = $searcher->page('//div[@id="p-num"]/span/a/text()')->search('葡萄酒 闪购','//div[@id="J_goodsList"]/ul/li/div/div/a/@href')->go();
        self::assertTrue(is_array($search_result));
        self::assertNotEmpty($search_result);
    }

    /**
     * Ensure iterate method without callback parameter passed work
     */
    public function testSearchIterate(){
        $searcher = new searchentry("https://search.jd.com/Search");
        $search_result = $searcher->totalpages('//div[@id=\'J_topPage\']/span/i')->search('葡萄酒 闪购','//div[@id="J_goodsList"]/ul/li/div/div/a/@href')->iterate()->go();
        self::assertTrue(is_array($search_result));
        self::assertNotEmpty($search_result);
    }

    /**
     *
     * Ensure iterate method with callback parameter passed work
     *
     */
    public function testSearchIterateWitchCallbackParameter() {
        /**
         *  Get products which not present in the search result pages by API
         */
        $getExtras = function($searchentry)  {
            $current_page = $searchentry->get_current_page();
            if ($current_page != -1) {
                $api_url = "https://search.jd.com/s_new.php?keyword=".$searchentry->keyword."&enc=utf-8&qrst=1&rt=1&stop=1&vt=2&suggest=1.his.0.0&page=".($current_page+1)."&s=29&scrolling=y&tpl=1_M";
//                var_dump("Api URL:");
//                var_dump($api_url);
                \requests::set_referer($searchentry->entry);
                \requests::$input_encoding="UTF-8";
                \requests::$output_encoding = "UTF-8";
                $api_result = \requests::get($api_url);
                $searchentry->extern(\selector::select($api_result,'//li/div/div/a/@href'));

            }
              };

        $searcher = new searchentry("https://search.jd.com/Search");
        $search_result = $searcher->totalpages('//div[@id=\'J_topPage\']/span/i')->search('葡萄酒 闪购','//div[@id="J_goodsList"]/ul/li/div/div/a/@href')->iterate($getExtras)->reset_totalpages(2,'*')->skip('even')->go();
        self::assertTrue(is_array($search_result));
        self::assertNotEmpty($search_result);
    }
}
