<?php
namespace UnitTest;

include_once("../app.php");
include_once("./ExtTestCase.php");
use Module\Pider;
use Module\IpSchedule\IpSchedule;

class IpScheduleTest extends ExtTestCase{

 public function testIpScheduleGetIpWithPider() {
     $pider = new Pider();
     $ipschedule = new IpSchedule(200);
     $ipschedule->source(function(){
        $api_uri = "http://api.ip.data5u.com/dynamic/get.html?order=038087c1874c84a753c17e8bb687a456&sep=3";
        $client = new \GuzzleHttp\Client();
        return $client->request('GET',$api_url);
     });
     $pider->register($ipshedule);
     $pider->usePlugin('SearchEntry');
     $pider->usePlugin('MultiTabs');
     $pider->base_url = 'www.taobao.com';
     $pider->keyword_param('keyword')->extra_param('enc=utf-8')->totalpages('//div[@id=\'J_topPage\']/span/i')->search('葡萄酒','//div[@id="J_goodsList"]/ul/li/div/div/a/@href')->iterate($getExtras)->reset_totalpages(2,'*')->skip('even')->go();
     $pider->run();
 }

 public function testIpScheduleGetIpAlone() {
     $ipschedule = new IpSchedule();
     $ipschedule->source(function(){
        $api_url = "http://api.ip.data5u.com/dynamic/get.html?order=038087c1874c84a753c17e8bb687a456&sep=3";
        $client = new \GuzzleHttp\Client();
        $response = @$client->request('GET',$api_url)->getBody()->getContents();
        return $response;
     });
     $ipschedule->run();
     for($i = 0,$limit =100; $i < $limit; $i++) {
         $ip = $ipschedule->deliver();
     };
 }

 public function testIpScheduleGetIpThroughOldApi() {
     $ipschedule = new IpSchedule();
     $ipschedule->source(function(){
        //request api
         $api_url = "http://xdeng.9kacha.com/agent_ip/getIp.php";
         $arr = array();
         $arr['app_id'] = '8257966c-3b9f-11e7-8b5a-5254000f5d7a';
         $arr['classify'] = 1;
         $arr['time'] = (string)time();
         $arr['rtoken'] = md5($arr['time'].'c617447662fdc20bd77477b8b8fcb03c');
         $json_arr = "jparams=".json_encode($arr); 
         $chandler = curl_init($api_url);
         curl_setopt($chandler,CURLOPT_POST,true);
         curl_setopt($chandler,CURLOPT_POSTFIELDS,$json_arr);
         curl_setopt($chandler,CURLOPT_RETURNTRANSFER,true);
         $json_result = @curl_exec($chandler);
         curl_close($chandler);
         //parse the data
         $result = json_decode($json_result,true);
         if (isset($result['err_code']) && $result['err_code'] == 0) {
             $ip = $result['data']['ip'];
             return $ip;
         }
         return false;
     });
     $ipschedule->run();
     for($i = 0,$limit =10; $i < $limit; $i++) {
         $ip = $ipschedule->deliver();
         var_dump($ip);
     };
 }
}
