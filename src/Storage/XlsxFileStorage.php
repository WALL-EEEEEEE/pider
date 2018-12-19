<?php
namespace Pider\Storage;

use Pider\Log\Log as Logger;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class XlsxFileStorage {
    private static $phpspreadsheet;

    public function __construct() {
        $log = Logger::getLogger();
        if($this->detect_dependency()) {
           $log->error("Xlsx read or write must need PhpSpreadsheet(https://phpspreadsheet.readthedocs.io/en/develop/) support, to use this feature, you need download it by composer at first.");
        }
    }
    public function detect_dependency() {
        $isLack= false;
        $dependencies = [
            'PhpOffice\PhpSpreadsheet\Spreadsheet'
        ];
        foreach($dependencies as $dependency) {
            if(!class_exists($dependency)) {
                $isLack = true;
            };
        }
        return $isLack;
    }
    public function writer() {
        return new XlsxWriter();
    }

    public function reader(String $filename = '') {
        return new XlsxReader($filename);
    }

    public static function getWriter() {
        return (new XlsxFileStorage())->writer();
    }
    public static function getReader(string $filename = '') {
        return (new XlsxFileStorage())->reader($filename);
    }
}
