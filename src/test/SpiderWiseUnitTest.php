<?php
define('PIDER_PATH',dirname(__DIR__));
define('APP_ROOT',dirname(__DIR__,2));

include(dirname(__FILE__,2).'/Pider.php');
use PHPUnit\Framework\TestCase;
use Pider\Support\SpiderWise;

class SpiderWiseUnitTest extends TestCase {

    public function testIsSpider() {
        $spiders = APP_ROOT.'/examples/company/TmallWineStatusSpider.php';
        $this->assertTrue(SpiderWise::isSpider($spiders));
    }

    public function testListSpiders() {
        $directory = APP_ROOT.'/examples/company';
        $this->assertNotEmpty(SpiderWise::listSpider($directory));
    }

    public function testLinkedSpidersWithoutURL() {
        $this->assertEmpty(SpiderWise::linkSpider());
    }

    public function testLinkedSpidersWithURL() {
        $spiders = [
                    "JdWineAllTagsSpider",     
                    "JdWineLinkConsistentCensor",
                ];
        $linked_spiders = SpiderWise::linkSpider('https://item.jd.com/16290805360.html');
        $this->assertEquals($linked_spiders,$spiders);
    }

}


