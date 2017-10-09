<?php
namespace Module\Template\Exception;

class FileNotFoundException extends \ErrorException{
    private $filename;
    public function __construct($filename,$message,$code) {
        $this->filename = $filename;
        $this->message = $message;
        $this->code = $code;
    } 

    public function __toString() {
        return $this->filename.$message;
    }
}
