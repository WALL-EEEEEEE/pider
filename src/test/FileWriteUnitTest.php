<?php
define('PIDER_PATH',dirname(__DIR__));
define('APP_ROOT',dirname(__DIR__,2));

include(dirname(__FILE__,2).'/Pider.php');
use PHPUnit\Framework\TestCase;

class FileWriteUnitTest extends TestCase {

    public function testCanWrite() {
        $file= fopen('/tmp/filewrite.txt','w+');
        $text = 'test for writing ';
        fwrite($file,$text,1024);
        fclose($file);
    }
}

