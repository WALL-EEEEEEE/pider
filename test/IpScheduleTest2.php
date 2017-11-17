<?php
include_once('../app.php');
use Module\IpSchedule\IpSchedule;

function testIpScheduleGetIpThroughOldApi() {
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


testIpScheduleGetIpThroughOldApi();
