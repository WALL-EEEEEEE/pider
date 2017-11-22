<?php
namespace Util;

use requests;
/**
 * Class api
 * This class is used to manage the api used by spider
 * @package util
 */
class Api {

    public static function getIp() {
        \requests::$input_encoding='UTF-8';
        \requests::$output_encoding='UTF-8';
        $api_uri = "http://api.ip.data5u.com/dynamic/get.html?order=038087c1874c84a753c17e8bb687a456&sep=3";
        $ip_regex = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+/i';
        requests::set_proxies(
            array("http"=>'',
            "https"=>'')
        );
        $ip  = requests::get($api_uri);
        if (preg_match($ip_regex,$ip)) {
            return "http://".$ip;
        }
        return '';
    }

    public static function proxy_wrapper($callback) {
        \requests::set_useragents(
            array(
                'Mozilla/5.0 (Windows; U; Windows NT 5.2) Gecko/2008070208 Firefox/3.0.1',
                'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0)',
                'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko    ) Chrome/0.2.149.27 Safari/525.13',
                'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.12) Gecko/20080219 Fi    refox/2.0.0.12 Navigator/9.0.0.6',
                'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko    ) Version/3.1 Safari/525.13',
                'Mozilla/5.0 (iPhone; U; CPU like Mac OS X) AppleWebKit/420.1 (KHTML, like Geck    o) Version/3.0 Mobile/4A93 Safari/419.3',
                'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)',
                'Mozilla/5.0 (Macintosh; PPC Mac OS X; U; en) Opera 8.0',
            )

        );
        $proxy_ip = Api::getIp();
        while(empty($proxy_ip)){
            usleep(1500000);
            $proxy_ip = Api::getIp();
        }
        if ($proxy_ip) {
            \requests::set_proxies(
                array("http"=>$proxy_ip,
                "https"=>$proxy_ip)
            );
            $callback();
        } else {

            printf("%s\n","Error: A unexcepted error occurred when get the proxy ip");
        }
    }
}


