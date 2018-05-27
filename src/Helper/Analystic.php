<?php
namespace Util;
/**
 * This class is used to analysis spider's data in database
 * Class analystic
 * @package util
 */

class Analystic {

    /**
     * @param $src string the table stored source data
     * @param $desc string the table compared with
     * @param $benchmark mixed the columns should to be compared
     * @param $extra_info extra information should be displayed in report
     * @return array report of compared result, the different item and the different item's numbers.
     */
    public static function compare($src,$desc,$benchmark = '', $extra_info = array()){

        $fields = array();
        if (empty($src) && empty($desc)) {
            var_dump("Parameter src and desc can't be set to empty");
            return false;
        }

        if (!empty($benchmark) && is_array($benchmark)) {
           foreach($benchmark as $field) {
               $fields[] = $field;
           }
        }

        if (!empty($benchmark) && is_string($benchmark)) {
            $fields[] = $benchmark;
        }
        if (!empty($extra_info) && is_array($extra_info)) {
            foreach($extra_info as $field) {
                if (!in_array($field, $fields)) {
                    $fields[] = $field;
                }
            }
        }
        $data_src = \db::get_all("select ${$fields} from ".$src);
        $data_desc = \db::get_all("select ${$fields} from ".$desc);
        //extract fields data
        if (empty($data_src) && empty($data_desc)) {
            var_dump("The src or desc data don't exists!");
            return false;
        }

        $extract_field_src = arrary();
        $extract_field_desc = array();
        if (!empty($bechmark) && is_string($benchmark)) {
            foreach($data_src as $item) {
                $extract_field_src[$benchmark][] = $item[$benchmark];
            }
            foreach($data_desc as $item) {
                $extract_field_desc[$benchmark][] = $item[$benchmark];
            }
        }

        if (!empty($benchmark) && is_array($benchmark)) {
            foreach($data_src as $item) {
               foreach($benchmark as $ebenchmark) {
                   $extract_field_src[$ebenchmark][] = $item[$ebenchmark];
               }
            }
            foreach($data_desc as $item) {
               foreach($benchmark as $ebenchmark) {
                   $extract_field_desc[$ebenchmark][] = $item[$ebenchmark];
               }
            }
        }
        //analyzing
        $analy_report= array();
        if (!empty($benchmark) && is_string($benchmark)) {
            $diffs = array_diff($extract_field_src[$benchmark], $extract_field_src[$benchmark]);
            $dnums = count($diffs);
            $analy_report[$benchmark] = array($dnums => $diffs);
        }
        if (!empty($benchmark) && is_array($benchmark)) {
            foreach($benchmark as $ebenchmark) {
                $diffs = array_diff($extract_field_src[$ebenchmark], $extract_field_desc[$ebenchmark]);
                $dnums = count($diffs);
                $analy_report[$benchmark] = array($dnums=>$diffs);
            }
        }
        return $analy_report;
    }
}

