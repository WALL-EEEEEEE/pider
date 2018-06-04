<?php
namespace Pider\Support;

/**
 * @class URLCenter
 *
 * URL repository manages tons of urls
 *
 */

use Pider\Storage\Cache;

class URLCenter {
    private $cache;

    public function __construct() {
        $this->cache = new Cache('/tmp/ue.txt',true);
    }

    public function getOne() {
        $url = $this->cache->readLine();
        return $url;
    }

    public function putOne($url) {
        if($this->_valid($url)) {
            $this->cache->writeLine($url);
        }
    }

    private function _valid($url) {
        $valid_reg = '/(https?|ftp|file):\/\/[-A-Za-z0-9+&@#\/%?=~_|!:,.;]+[-A-Za-z0-9+&@#\/%=~_|]/i';
        if (preg_match($valid_reg,$url)) {
            return true;
        }
        return false;
    }
}
