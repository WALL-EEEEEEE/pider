<?php
namespace UnitTest;
include_once('./ExtTestCase.php');
use  Util\Savenger;
use  Util\Collectable;
use  UnitTest\ExtTestCase;


/**
 *UnitTest for Savenger class
 */
class SavengerTest  extends ExtTestCase implements Collectable{

    private $savenger;
    private $urls;
    private $expected_urls;
    /**
     * Prepare for some data
     */
    public function SetUp(){
        $this->savenger = new Savenger($this);
        $this->urls = [
            "http://item.jd.com/11654524887.html",
            "http://item.jd.com/3628478.html",
            "http://item.jd.com/1025120569.html",
            "http://item.jd.com/15224008274.html",
            "http://item.jd.com/1609747988.html",
            "http://item.jd.com/1411527145.html",
            "http://item.jd.com/1569539704.html",
            "http://item.jd.com/11479564983.html",
            "http://item.jd.com/4861515.html",
            "http://item.jd.com/12314803790.html",
            "http://item.jd.com/1308542603.html",
            "http://item.jd.com/3030375.html",
            "http://item.jd.com/4148200.html",
            "http://item.jd.com/12695417111.html",
            "http://item.jd.com/13851071782.html",
            "http://item.jd.com/4838984.html",
            "http://item.jd.com/12459959803.html",
            "http://item.jd.com/2512939.html",
            "http://item.jd.com/10840240490.html",
            "http://item.jd.com/10691298721.html",
        ];
        $this->expected_urls = array_filter($this->urls, function($key) {
            return $key %2 == 0;
        },ARRAY_FILTER_USE_KEY);

    }
    public function testSavengerCollectSuccessfully() {
        $url_loop = function() {
            for($i = 0; $i < 20; $i++) {
                if($i % 2 == 0) {
                    return $this->urls[$i];
                }
            }
        };
        $this->savenger->collect($url_loop);
        $url2_loop = function() {
            for($i = 1; $i < 20; $i++) {
                if($i % 2 == 0) {
                    return $this->urls[$i];
                }
            }
        };
        $this->savenger->collect($url2_loop);
        $urls = $this->getProperty($this->savenger,'garbage_factory');
        $this->assertArraySubset($urls,array_values($this->expected_urls));
    }

    public function  testSavengerRecycleSuccessfully() {
        $urls = $this->getProperty($this->savenger,'garbage_factory');
        $recycle_urls = array();
        while(!$this->savenger->empty()) {
            $recycle_urls[] = $this->savenger->recycle();
        }
        $this->assertEquals(array_reverse($recycle_urls),$urls);
    }

}



