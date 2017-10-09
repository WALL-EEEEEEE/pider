<?php
namespace Module;

use Module\Template\TemplateEngine as Template;
use requests;

/**
 * @class Pider
 * Handle all spider operation 
 */
class Pider {
    use Template;
    protected $urls;
    protected $domains;
    private $responses;

    public function __construct() {
    }

    public function go() {
        if (is_string($this->urls)) {
            $urls = array($this->urls);
        }
        foreach($urls as $url) {
            $response = requests::request($url);
            if (!empty($response)) {
                $this->responses[] = $response;
            }
        }
    }
}

