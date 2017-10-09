<?php
namespace Extension\Template;

use Exception\FileNotFoundException;
/**
 * @trait TemplateEngine 
 * Parsing the spider template file .json
 *
 */
trait TemplateEngine {
    use BaseParser;
    use StateParser;
    /**
     * @var dir
     * The template file dir
     */
    static $dir; 
    private $template;
    private $jsonObject; 
    public function __construct($template) {
        $this->EnsureTemplateExistence($template);
        $this->Init();
    }
    private function EnsureTemplateExistence($template) {
        $location = $dir.'/'.$template;
        if (!file_exists($location)){
            throw new FileNotFoundException($template,'Template file not found');
        }
        $this->template = $template;
    }
    private function Init() {
        $jsonstr = file_get_contents($this->template);
        if (empty($jsonstr)) {
            throw new TemplateParseException($template,'Empty template file');
        }
        $jsonObject = json_decode($jsonstr,true);
        if (is_null($jsonObject)) {
            throw new TemplateParseException($template,"Invalid json syntax");
        }
        $this->jsonObject = $jsonObject;
    }
    public function template(TemplateEngine $template) {
        $object = new static();
        $object->url = $this->jsonObject['url'];
        $object->rules = $this->jsonObject['rules'];
        $object->export = $this->jsonObject['export'];
        return $object;
    }

}
