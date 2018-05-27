<?php
namespace Pider\Template;

use Pider\Template\Exception\FileNotFoundException;
use Pider\Template\Exception\TemplateParseException;
use Pider\Template\Parser\BaseParser as BaseParser;
use Pider\Template\Parser\StateParser as StateParser;
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

    private function EnsureTemplateExistence($template) {
        $location = self::$dir.'/'.$template;
        if (!file_exists($location)){
            throw new FileNotFoundException($template,'Template file not found');
        }
        $this->template = $location;
    }
    private function Init() {
        $this->EnsureTemplateExistence($this->template);
        $jsonstr = file_get_contents($this->template);
        if (empty($jsonstr)) {
            throw new TemplateParseException($this->template,'Empty template file');
        }
        $jsonObject = json_decode($jsonstr,true);
        if (is_null($jsonObject)) {
            throw new TemplateParseException($this->template,"Invalid json syntax");
        }
        $this->jsonObject = $jsonObject;
    }
    public function template($template) {
        $this->template = $template;
        $this->Init();
        $this->parse();
        $object = new static();
        $object->url = $this->jsonObject['url'];
        $object->rules = $this->jsonObject['rules'];
        $object->export = $this->jsonObject['export'];
        return $object;
    }
    protected function parse() {
        $refcls = new ReflectionClass(static::class);
        $traits = $refcls->getTraits();
        if (!empty($traits)) {
            foreach($traits as $name => $trait) {
                $parse = $trait->getMethod('parse');
                $parse->setAccessible(true);
                $parse->invoke($name,$this->jsonObject);
            }
        }
    }
}
