<?php
namespace Exception;

class TemplateParseException extends ErrorException {
    private $template;

    public function __construct($template,$message,$code) {
        $this->template = $template;
        $this->message = $message;
        $this->code = $code;
    }
    public function __toString() {
        return $template.$message;
    }
}

