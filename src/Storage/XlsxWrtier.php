<?php
namespace Pider\Storage;

use Pider\Log\Log as Logger;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
/**
 * @class XlsxWriter
 * write for Xlsx
 *
 */

class XlsxWriter {
    private $writer;
    private $spreadsheet;
    private $actived_sheet;

    public function __construct() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $this->actived_sheet = $sheet;
        $this->spreadsheet = $spreadsheet;
    }

    public function fromArray(array $data) {
        $this->actived_sheet->fromArray($data);
    }

    public function save($file) {
        $writer = new Xlsx($this->spreadsheet);
        $this->writer->save($file);
    }

}



