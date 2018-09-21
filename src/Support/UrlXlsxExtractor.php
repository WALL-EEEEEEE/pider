<?php
namespace Pider\Support;

use Pider\Storage\XlsxFileStorage as Xlsx;

class UrlXlsxExtractor {
    private $default_title =  'url';
    private $xlsx_data;

    public function __construct(String $filename) {
        $xlsx = Xlsx::getReader($filename);
        $data = $xlsx->toArray();
        $this->xlsx_data = $data;
    }

    public function extract($url_title = '') {
        $title = '';
        if (!empty($url_title)) {
            $title = $url_title;
        } else {
            $title =  $this->default_title;
        }
        $column_titles  = $this->xlsx_data[0];
        $column_titles  = array_map(function($value) {
            return strtolower($value);
        },$column_titles);
        $title = strtolower($title);
        if (in_array($title,$column_titles)) {
            $url_data = [];
            $title_index = array_search($title,$column_titles);
            foreach($this->xlsx_data as $row_no =>  $row) {
                if($row_no !== 0) {
                    $url_data[] = $row[$title_index];
                }
            }
            return $url_data;
        }
        return [];
    }
}

