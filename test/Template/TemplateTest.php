<?php
namespace UnitTest\Template;
include_once('../ExtTestCase.php');
use UnitTest\ExtTestCase;
use Module\Template\TemplateEngine;
use Module\Pider;


/**
 * UnitTest for Piders template engine
 */

class TemplateTest extends ExtTestCase {

    public function SetUp() {
        TemplateEngine::$dir = APP_ROOT.'/templates';
    }

    public function testTemplateSuccess() {
        $pider = new Pider();
        $pider->template('jd.template.json');
        $pider->go();
    }
}
