<?php

namespace Pider\Storage;

use Pider\Log\Log as Logger;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IoFactory;
/**
 * @class XlsxWriter
 * write for Xlsx
 *
 */

class XlsxReader{
    private $spreadsheet;
    private $actived_sheet;
    private $reader;

    public function __construct(string $filename = '') {
        $reader = IoFactory::createReader('Xlsx');
        $this->reader = $reader;
        if (!empty($filename)) {
            $this->load($filename);
        }
    }

    public function load(string $filename) {
        $spreadsheet = $this->reader->load($filename);
        $sheet = $spreadsheet->getActiveSheet();
        $this->actived_sheet = $sheet;
        $this->spreadsheet = $spreadsheet;
    }

    public function toArray() {
        $arrays = [];
        foreach ($this->actived_sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(False);
            $col_arrays = [];
            foreach($cellIterator as $cell) {
                $col_arrays[] = $cell->getValue();
            }
            $arrays[] = $col_arrays;
        }
        return $arrays;
    }

}
