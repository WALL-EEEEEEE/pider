<?php
include_once(__DIR__."/../../../app.php");
use Model\UrltagModel;
use Extension\DBExtension;
use Util\Common;
use Util\Data\Required as DataRequired;

$GLOBALS['website']['id'] = 1;
DBExtension::switch_db('phpspider');

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
    $tagmaps_types = array_keys($tagmaps);
    $first_types = [];
    $second_types = [];

    //将分类分为两级
    foreach($tagmaps as $name => $tagmap) {
        if (count(array_intersect($tagmap,$tagmaps_types)) > 1 || (count($tagmap) == 1 && count(array_intersect($tagmap,$tagmaps_types))== 1)) {
            $first_types[$name] = $tagmap;
        } else {
            $second_types[$name] = $tagmap;
        }
    }
    foreach($tags as $ah_id =>  $tag) {
        $new_tags = [];

        foreach($tag as  $single_tag) {

            //生成二级分类
            foreach($second_types as $type =>  $tagmap) {
                foreach($tagmap as $rule) {
                    if (!in_array($rule, $tagmaps_types)) {
                        $rule = '/'.preg_replace('/x+/','\d',$rule).'/';
                        if (preg_match($rule,$single_tag)) {
                            $new_tags[] = $type;
                        }
                    }
                }
            }
            $new_tags= array_unique($new_tags);
            //生成一级分类
            foreach($first_types as $type => $tagmap) {
                    if(count(array_intersect($tagmap,$new_tags))>= 1) {
                        $new_tags[] = $type;
                    }
                }
            }

        $new_tags = array_unique($new_tags);
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

/**
 * 过滤掉已经发现的遗弃标签
 */
function FilterDesertFlagTags($tags){
    $cached_desert_tag_path = APP_ROOT.'/cache/.data/dtags.db';
    $cached_desert_tags = [];
    if (!file_exists($cached_desert_tag_path)) {
        return $tags;
    } else {
        $cached_desert_tags = json_decode(file_get_contents($cached_desert_tag_path),true);
        if (empty($cached_desert_tags)) {
            return $tags;
        } else {
            foreach($tags as $key=>$tag) {
                if (in_array($tag,$cached_desert_tags)) {
                    unset($tags[$key]);
                }
            }
            return $tags;
        }
    }
}

/**
 * 过滤掉已经发现的有用标签
 */
function FilterUsefulFlagTags($tags){
    $cached_useful_tag_path = APP_ROOT.'/cache/.data/utags.db';
    $cached_useful_tags = [];
    if (!file_exists($cached_useful_tag_path)) {
        return $tags;
    } else {
        $cached_useful_tags = json_decode(file_get_contents($cached_useful_tag_path),true);
        if (empty($cached_useful_tags)) {
            return $tags;
        } else {
            foreach($tags as $key=>$tag) {
                if (in_array($tag,$cached_useful_tags)) {
                    unset($tags[$key]);
                }
            }
            return $tags;
        }
    }

}

/**
 * 发现新的标签
 */
function DiscoverTags() {
    $org_all_tags = DataRequired::require('tagnames',"select DISTINCT url_tag.tag_desc from url_tag,all_html where url_tag.ah_id = all_html.uid and tag_desc is not null and  website_id = ".$GLOBALS['website']['id']);
    $all_tags = [];
    foreach($org_all_tags as $tag) {
        $all_tags[] = $tag['tag_desc'];
    }
    //移除标记为遗弃的标签
    $all_tags = FilterDesertFlagTags($all_tags);
    $all_tags = FilterUsefulFlagTags($all_tags);
    $used_tags = [];
    $tagmappings = get_all_tagmap();
    foreach($tagmappings as $tagmapping) {
        $used_tags = array_merge($used_tags,$tagmapping);
    }
    foreach($all_tags as $a_key => $a_name) {
        foreach($used_tags as $u_name) {
            $generated_pattern = '/'.preg_replace('/x+/i',"\d+",$u_name).'/i';
            if (preg_match($generated_pattern,$a_name)) {
                unset($all_tags[$a_key]);
                break;
            }
        }
    }
    //缓存ewtags.db文件
    $cached_file = APP_ROOT.'/cache/.data/newtags.db';
    file_put_contents($cached_file,json_encode($all_tags));
}

function jd(){
    DiscoverTags();
    $tagmappings = get_custom_tagmap();
    $org_tags = pull_tag_info();
    post_process($org_tags,$tagmappings); 
}

if (PHP_SAPI != 'cli') {
    printf("This script must be run under cli!");
    exit(0);
} else {
    jd();
}
