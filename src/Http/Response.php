<?php
namespace Pider\Http;

use GuzzleHttp\Psr7\Response as BaseResponse;

class Response {
    private $response = '';
    private $inputEncode =  '';
    private $outputEncode = '';
    private $url = '';
    private $org_url = '';
    public  $callback;
    

    public function __construct(BaseResponse $baseResponse){
        $this->response = $baseResponse;
    }

    public static function formResponse() {
        return new Response(new BaseResponse());
    }

    public function inputEncode(string $encode) {
        $this->inputEncode = $encode;
        return $this;
    }

    public function outputEncode(string $encode) {
        $this->outputEncode = $encode;
        return $this;
    }

    public function getUrl() {
        return (string)$this->url;
    }

    public function setUrl(string $url) {
        $this->url =  $url;
    }

    public function getOrgUrl() {
        return (string) $this->org_url;
    }

    public function setOrgUrl(string $url) {
        $this->org_url = $url;
    }
    public function getText() {
        $content_type = @$this->response->getHeader('Content-Type')[0];
        $content_charset = @trim(strstr($content_type,'charset='),'charset=');
        if (!empty($this->inputEncode)) {
            $content_charset = $this->inputEncode;
        }
        if (empty($content_charset) || empty($this->outputEncode) ) {
            return (string) $this->response->getBody();
        } else {
            $body =  (string)$this->response->getBody();
            if (!empty($body)) {
                $body = iconv($content_charset,$this->outputEncode.'//IGNORE',$body);
                }
                return $body;
        }
    }

 
    public function xpath($xpath): Selector {
        $contents  = $this->getText(); 
        $selector = new Selector($contents);
        return $selector->xpath($xpath);
    }

    public function jquery($jquery):Selector {
        $contents  = $this->getText(); 
        $selecctor = new Selector($contents);
        return $selecteor->jquery($jquery);
    }
    public function css($css):Selector {
        $contents = $this->getText();
        $selector = new Selector($contents);
        return $selector->css($css);
    }

    public function __call($method,$args) {
        return $this->response->$method($args);
    }
}
