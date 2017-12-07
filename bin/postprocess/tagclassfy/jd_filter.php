<?php
include_once("../../../app.php");

use Model\UrltagModel;
use Extension\DBExtension;
$GLOBALS['website']['id'] = 1;
DBExtension::switch_db('phpspider');

function delete_url_filter_yesterday($website_id) {
       $sql = '';
       $sql = "delete from url_tag_filter where url_tag_filter.ctime<'".date('Y-m-d')."'";  
       $result = DBExtension::query($sql);
       if (!$result) {
           return false;
       }
       return true;

}

function get_custom_tagmap() {
    $mapping_file = __DIR__."/../../../cache/.data/tagmappings";
    $tagmappings = array();
    if (!file_exists($mapping_file)) {
        throw new ErrorException("Tag mapping file not exists!");
    }
    $tagmappings = json_decode(file_get_contents($mapping_file),true);
    if (!empty($tagmappings)) {
        return $tagmappings;
    } else {
        return false;
    }
}


function get_all_tagmap() {
    $base_mapping_file = __DIR__."/../../../cache/.data/basetagmappings";
    $tagmappings = array();
    $basetagmappings = array();
    $custom_tagmappings = get_custom_tagmap();
    //load base mapping file
    if (!file_exists($base_mapping_file)) {
        throw new ErrorException("base tag mapping file not exists!");
    }
    $basetagmappings = json_decode(file_get_contents($base_mapping_file),true);
    if (!empty($basetagmappings)){
        $tagmappings = array_merge($basetagmappings,$custom_tagmappings);
    } else {
        $tagmappings = $basetagmappings;
    }
    return $tagmappings;
}

/**
 *@method pull the tags to be processed 
 */
function pull_tag_info() {
    $urltag = new UrltagModel();
    $org_tags = $urltag->get_by_website_id($GLOBALS['website']['id']);
    $dest_tags = []; 
    if (!empty($org_tags)) {
        foreach($org_tags as $tag) {
            $dest_tags[] = $tag;
        }
    }
    if(empty($dest_tags)) {
        return false;
    }
    return $dest_tags;
}


/**
 *@method  filter the tags
 *@param $tags orginal tags to be classfied
 *@param $tagmap rules classfication based on
 *@return array type tags should be attached to product
 */
function post_filter($tags,$tagmaps){
    $filter_tags = [];
    $tagmaps_types = array_keys($tagmaps);
    $first_types = [];
    $second_types = [];
    $customize_types_detail = [];
    //将分类分为两级
    foreach($tagmaps as $name => $tagmap) {
        if (count(array_intersect($tagmap,$tagmaps_types)) > 1 || (count($tagmap) == 1 && count(array_intersect($tagmap,$tagmaps_types))== 1) || $name == '奢侈美酒') {
            $first_types[$name] = $tagmap;
        } else {
            $second_types[$name] = $tagmap;
        }
    }
    foreach($first_types as $type_name => $type_items) {
        foreach($type_items as $type_item) {
            if (array_key_exists($type_item,$second_types)) {
                if (!array_key_exists($type_name,$customize_types_detail)) {
                    $customize_types_detail[$type_name] = $second_types[$type_item];
                } else {
                    $customize_types_detail[$type_name] = array_merge($customize_types_detail[$type_name],$second_types[$type_item]);
                }
            }

        }
    }
    foreach($tags as $tag) {
            //生成二级分类
            foreach($second_types as $type =>  $tagmap) {
                foreach($tagmap as $rule) {
                    if (!in_array($rule, $tagmaps_types)) {
                        $tagtype = '';
                        foreach($customize_types_detail as $cname => $ctypes) {
                            if (in_array($rule,$ctypes)){
                                $tagtype = $cname;
                            }
                        }
                       $rule = '/('.preg_replace('/x+/','\d',$rule).')/';
                       if (preg_match($rule,$tag['tag_desc'],$matches)) {
                                $tag['tag_desc'] = $matches[1];
                                $tag['type_name'] = $tagtype;
                                $filter_tags[] = $tag;
                       }
                    }
                }
            }
    }
    //pouring the filtered tag type into database
    foreach($filter_tags as $tags) {
            $url_tag = new UrltagModel();
            $url_tag->table("url_tag_filter");
            $url_tag->fromArray($tags);
            $url_tag->add();
    }
    echo 'Filter process .... done '.PHP_EOL;
}



function jd_filter(){
    $tagmappings = get_custom_tagmap();
    $org_tags = pull_tag_info();
    delete_url_filter_yesterday($GLOBALS['website']['id']);
    post_filter($org_tags,$tagmappings); 
}

if (PHP_SAPI != 'cli') {
    printf("This script must be run under cli!");
    exit(0);
} else {
    jd_filter();
}
