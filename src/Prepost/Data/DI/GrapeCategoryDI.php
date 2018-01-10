<?php
namespace Module\Data\DI;
use Extension\DBExtension;

class GrapeCategoryDI extends DataInject {
    public function init(){
        DBExtension::switch_db('phpspider');
        $grapeCates = DBExtension::get_all("select grape_variety_ch,grape_variety_en from grape_variety_init");
        $initData = [];
        foreach($grapeCates as  $grapeCate ) {
            $initData[$grapeCate['grape_variety_ch']] = $grapeCate['grape_variety_en'];
        }
        return $initData;
    }
    public function update(){

    }
    public function persist() {
        $cache_dir= APP_ROOT.'/cache/di/';
        $cache_file = $cache_dir.'/'.base64_encode(basename(str_replace('\\','/',__CLASS__)));
        if (file_exists($cache_file)) {
            return false;
        }

        if (!file_exists($cache_dir)) {
            mkdir($cache_dir);
        }
        $cached_content = json_encode($this->data);
        file_put_contents($cache_file,$cached_content);
    }

    public function load():array {
        $cache_dir= APP_ROOT.'/cache/di/';
        $cache_file = $cache_dir.'/'.base64_encode(basename(str_replace('\\','/',__CLASS__)));
        if (file_exists($cache_file)){
            $raw_data = json_decode(file_get_contents($cache_file),true);
            $this->data = $raw_data;
        } else {
            $this->data = $this->init();
            $this->persist();
        }
        return $this->data;
    }

}
