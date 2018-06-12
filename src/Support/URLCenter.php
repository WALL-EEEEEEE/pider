<?php
namespace Pider\Support;

/**
 * @class URLCenter
 *
 * URL repository manages tons of urls
 *
 */

use Pider\Storage\Cache;
use Pider\Support\URLSource;

class URLCenter {
    private $cache;
    private $sources = [];
    private $urls;
    private $memory_limit;

    public function __construct() {
        $this->cache = new Cache('/tmp/ue.txt',true);
    }

    public function init() {
        $urls = [];
        foreach($this->sources as $source) {
            $url_collected = $source->suck();
            if (is_string($url_collected) && $this->_valid($url_collected)) {
                $urls[] = $url_collected;
            } else if (is_array($url_collected)) {
                foreach($url_collected as $url) {
                    if (is_string($url) && $this->_valid($url)) {
                        $urls[] = $url;
                    }
                }
            }
        }
        $this->urls = $urls;
        echo "Total urls in URLCenter: ".count($urls).PHP_EOL;
    }

    public function getOne() {
        $url = array_shift($this->urls);
        echo "Total urls in URLCenter: ".count($this->urls).PHP_EOL;
        return $url;
    }

    public function putOne($url) {
        if($this->_valid($url)) {
            array_push($this->urls,$url);
        }
        echo "Total urls in URLCenter: ".count($urls).PHP_EOL;
    }

    private function _valid($url) {
        $valid_reg = '/(https?|ftp|file):\/\/[-A-Za-z0-9+&@#\/%?=~_|!:,.;]+[-A-Za-z0-9+&@#\/%=~_|]/i';
        if (preg_match($valid_reg,$url)) {
            return true;
        }
        return false;
    }

    public function addSource(URLSource $source) {
        $this->sources[] = $source;
    }
}
