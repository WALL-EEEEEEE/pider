<?php
include_once(__DIR__."/../../../app.php");
include_once(__DIR__."/../../../Util/Common.php");
use Model\UrltagModel;
use Extension\DBExtension;
$GLOBALS['website']['id'] = 1;
DBExtension::switch_db('phpspider');

function get_tagmap() {
    $mapping_file = __DIR__."/../../../cache/.data/tagmappings";
    $tagmappings = array();
    if (!file_exists($mapping_file)) {
        throw new ErrorException("Tag mapping file not exists!");
    }
    $tagmappings = json_decode(file_get_contents($mapping_file),true);
    if (!empty($tagmappings)) {
        return $tagmappings;
    }
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
            $dest_tags[$tag['ah_id']][] = $tag['tag_desc'];
        }
    }
    if(empty($dest_tags)) {
        return false;
    }
    return $dest_tags;
}


/**
 *@method classfy the tags and attach a specify tag to the product
 *@param $tags orginal tags to be classfied
 *@param $tagmap rules classfication based on
 *@return array type tags should be attached to product
 */
function post_process($tags,$tagmaps){
    $new_tags_map = [];
    foreach($tags as $ah_id =>  $tag) {
        $new_tags = [];
        foreach($tag as  $single_tag) {
            foreach($tagmaps as $type =>  $tagmap) {
                foreach($tagmap as $rule) {
                    $rule = '/'.preg_replace('/x+/','\d',$rule).'/';
                    if (preg_match($rule,$single_tag)) {
                        $new_tags[] = $type;
                    }
                }
            }
        }
        if (!empty($new_tags)) {
            $new_tags_map[$ah_id] = array_unique($new_tags);
        }
    }
    //pouring the custommized tag type into database
    $tag_type_datas = array();
    foreach($new_tags_map as $ah_id => $tag_types) {
        foreach($tag_types as $tag_type) {
            $tag_type_data = array();
            $tag_type_data["ah_id"] = $ah_id;
            $tag_type_data['uid'] = spawn_guid();
            $tag_type_data['type_name'] = $tag_type; 
            $tag_type_data['ctime'] = date("Y-m-d H:i:s");
            $url_tag = new UrltagModel();
            $url_tag->table("url_tag");
            $url_tag->fromArray($tag_type_data);
            $url_tag->add();
        }
   }
} 
function jd(){
    $tagmappings = get_tagmap();
    $org_tags = pull_tag_info();
    post_process($org_tags,$tagmappings); 

}

if (PHP_SAPI != 'cli') {
    printf("This script must be run under cli!");
    exit(0);
} else {
    jd();
}
