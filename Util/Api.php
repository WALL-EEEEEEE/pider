<?php
namespace Util;

use Util\Http;
/**
 * Class api
 * This class is used to manage the api used by spider
 * @package util
 */
class Api {

   /**
    *  Get the proxy ips
     * @param string $classify
     * @return bool|string
   */
   public static  function getIp($classify="1")
    {
        //请求数据API
        $api_url = "http://xdeng.9kacha.com/agent_ip/getIp.php";
        $arr = array();
        $arr['app_id'] = '8257966c-3b9f-11e7-8b5a-5254000f5d7a';
        $arr['classify'] = $classify;
        $arr['time'] = (string)time();
        $arr['rtoken'] = md5($arr['time'].'c617447662fdc20bd77477b8b8fcb03c');
        $json_arr = "jparams=".json_encode($arr);
        $chandler = curl_init($api_url);
        curl_setopt($chandler,CURLOPT_POST, true);
        curl_setopt($chandler, CURLOPT_POSTFIELDS, $json_arr);
        curl_setopt($chandler, CURLOPT_RETURNTRANSFER,true);
        $json_result = @curl_exec($chandler);
        curl_close($chandler);
        //解析数据
        $result = json_decode($json_result,true);
        if (isset($result['err_code']) && $result['err_code'] == 0)
        {
            $ip = "http://" . $result['data']['ip'];
            return $ip;
        }
        return false;
    }


    public static function get_standard_products_url($limited,$website) {
        $api_url = "https://9edit.9kacha.com/api/get_web_url.php";
        $count_jparams = 'jparams={"web_url":"'.$website.'","page_num":1,"limit":1,"atoken":"00f41bf3f58565fc44104b563dcbff10","time":"1486526649"}';
        $count_result = http::post($api_url,$count_jparams);
        $all_count = -1;
        $result = array();
        //获取标准库中的商品总数
        if (!empty($count_result)) {
            $count_result = json_decode($count_result,true);
            if (isset($count_result['data']['all_count'])) {
                $all_count = $count_result['data']['all_count'];
            }

        }

        if ($all_count <= 0 ) {
            echo "API Error: Can't get the amount of products url in $website";
            exit(0);
        }
        //分批次从接口中获取全部数据
        $loops = $all_count%$limited ? intval($all_count/$limited)+1: intval($all_count/$limited);
        for ($i = 1; $i <= $loops; $i++){
            $jparams =  'jparams={"web_url":"'.$website.'","page_num":'.$i.',"limit":'.$limited.',"atoken":"00f41bf3f58565fc44104b563dcbff10","time":"1486526649"}';
            $per_result = http::post($api_url,$jparams);
            if (!empty($per_result)) {
                $per_result = json_decode($per_result,true);
                if (isset($per_result['data']['urls'])) {
                    $products = $per_result['data']['urls'];
                    if (is_array($products) ) {
                        foreach($products as $product) {
                            $result[] = $product['product_url'];
                        }
                    } else {
                        echo "API Error: Broken data!";
                        exit(0);
                    }

                } else {
                    echo "API Error: Broken data!";
                    exit(0);
                }
            } else {
                echo "API Error: Can't get the products url of $website!";
                exit(0);
            }
        }

        return $result;
    }
}

