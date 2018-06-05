<?php
define('PIDER_PATH',dirname(__DIR__));
define('APP_ROOT',dirname(__DIR__,2));

include(dirname(__FILE__,2).'/Pider.php');
use PHPUnit\Framework\TestCase;
use Pider\Support\SpiderWise;

class SpiderWiseUnitTest extends TestCase {

    public function testIsSpider() {
        $spiders = '/home/jhbian/pider/examples/company/TmallWineStatusSpider.php';
        var_dump(SpiderWise::isSpider($spiders));
    }

    public function testListSpiders() {
        $directory = '/home/jhbian/pider/examples/company';
        var_dump(SpiderWise::listSpider($directory));
    }
}


