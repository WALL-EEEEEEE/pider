<?php
namespace Module\IpSchedule;

use Module\Http\Request;

class IpSource1 extends IpSource {
    public function suck() {
        $api_uri = "http://api.ip.data5u.com/dynamic/get.html?order=038087c1874c84a753c17e8bb687a456&sep=3";
        $ip_regex = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+/i';
        $request = new Request();
        $ip  = $request->get($api_uri);
        if (preg_match($ip_regex,$ip)) {
            return "http://".$ip;
        }
        return '';
    }
}
