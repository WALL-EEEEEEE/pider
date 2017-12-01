<?php
namespace Module\Http;

class Selector {

    private static $xml_str= '';
    private static $selected = '';

    public function __construct($xml_str) {
        self::$xml_str = $xml_str;
    }

    public static function xpath(string $xpath, string $xml_str = '' ) {
        $simpleXml = '';
        if (empty($xml_str)) {
            $xml_str = self::$xml_str;
        } 

        $xml_str = mb_convert_encoding($xml_str, "HTML-ENTITIES", "UTF-8");
        $HtmlDocument = new \DomDocument(); 
        @$HtmlDocument->loadHTML($xml_str);
        $DomXpath = new \DomXpath($HtmlDocument);
        $selector = new Selector(self::$selected);
        $selector::$selected = $DomXpath->query($xpath);
        return $selector;
    }

    public static function query(string $query , string $xml_str = '') {
    }

    public static function css (string $css, string $xml_str = '') {

    }

    public function extract() {
        $elemens = [];
        foreach (self::$selected as $element) {
            $elements[] = trim($element->nodeValue);
        }
        return $elements;
    }

    public function extract_first() {
        if (is_array($this->selected) && !empty($this->selected)) {
            return $this->selected[0];
        } else {
            return  $this->selected;
        }
    }
}

