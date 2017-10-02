<?php
namespace  Util;

class Http extends \requests {

   private static $proxy ='';
    /**
     * @TODO check a url is passed by $url parameter
     * @param $url
     * @return array|bool
     */
   public static function get_http_header($url, $request_type="HEAD",$timeout = 3) {
        $CURL_REQUEST_TYPE = -1;
        $assoc_arr = array(
            "POST"=> CURLOPT_POST,
            "GET" => CURLOPT_HTTPGET,
            "PUT" => CURLOPT_PUT,
            "HEAD"=> CURLOPT_NOBODY,
        );

        if (empty($url)) {
            printf("%s\n","Argument: URL cann't be empty!");
            return false;
        };

        if(!is_numeric($timeout) || intval($timeout) < 0 ) {
            printf('%s\n',"Argument: timeout must be a positive number");
            return false;
        }

        if (!empty($request_type) && is_string($request_type)) {
            $CURL_REQUEST_TYPE = $assoc_arr[$request_type];
        } else {
            echo "Error, Request type must be specified as a string, and one of values \"GET,POST,HEAD,PUT\"";
            exit(0);
        }
        $chandler = curl_init($url);
        $option_arr = array(
            CURLOPT_AUTOREFERER => true,
            CURLOPT_HEADER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FILETIME => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT=>$timeout,
            CURLOPT_CONNECTTIMEOUT=>$timeout 
        );
        curl_setopt_array($chandler,$option_arr);
        //specify the request type,default as HEAD
        if ($CURL_REQUEST_TYPE > 0) {
            curl_setopt($chandler, $CURL_REQUEST_TYPE, true);
        }

        if (!empty(self::$proxy)) {
            curl_setopt($chandler,CURLOPT_PROXY, self::$proxy);
        }
        $cresult = curl_exec($chandler);
        //过滤得到http头字段
        $header = array();
        if (!curl_errno($chandler)) {
            $cinfo  = curl_getinfo($chandler);
            if (empty($cinfo)) {
                \log::error("A error occurred in get $url's http header!");
                return false;
            }
            $header['http_code'] = !empty($cinfo['http_code'])? trim($cinfo['http_code']):"";
            $header['content_length'] = !empty($cinfo['download_content_length']) && $cinfo['download_content_length'] > 0 ? trim($cinfo['download_content_length']):"";
            $header['last_modified'] = !empty($cinfo['filetime']) && $cinfo['filetime'] > 0 ? trim($cinfo['filetime']):"";
            //提取etag字段
            $etag_alias = array(
                "etagid",
                "eagleid",
                "eagleeye-traceid",
            );
            $etag_regex = "/^.*(?:";
            foreach ($etag_alias as $alia) {
                $alia = addcslashes($alia,'-');
                $etag_regex .= $alia."|";
            }
            $etag_regex = trim($etag_regex,'|');
            $etag_regex .= "):(.*)$/mi";
            if (preg_match($etag_regex,$cresult, $etag_result)) {
                $header['etag'] = empty($etag_result[1])?"":trim($etag_result[1]);
            } else {
                $header['etag'] = "";
            }

            curl_close($chandler);
            return $header;
        } else {
            \log::error("A error occurred in get $url's http header!");
            return false;
        }
    }

    /**
     * set proxy ip
     * @param $proxy
     */
    public static function set_proxy($proxy) {
        if (empty($proxy)) {
            return false;
        }
        self::$proxy = $proxy;
        return true;
    }

    public static function unset_proxy() {
       if (!empty($proxy)) {
           self::$proxy = '';
       }
    }
}

