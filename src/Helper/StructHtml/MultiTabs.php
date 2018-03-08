<?php

namespace Util\StructHtml;


/**
 * Class MultiTabs 
 *
 * This class is a helper tool that can process structed html with multi-tabs  well.
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/9/7
 * Time: 11:32
 */

class MultiTabs {

    /** Get all sepcified elements from multiple tab pages.
     *  Tab page is a separated webpage accessiablee as a tab in parent page.
     * @param string|array $urls        specify url included to search
     * @param string|array $selectors   specify elements included to search.
     * @param string|array $tabnames    specify tabs included to search elements.
     * @param string $tabtype           specify the way concatenating tabname with $urls or $baseurl and construting a new url.
     * @param string $baseurl           if a $baseurl exists, then it will be used to concatenate with tabname,or $urls will be used.
     * @return array|bool               return false if failing to  get elements,or return a elements array
     */
  public static function get_all_elements($urls,$selectors,$tabnames='',$tabtype='slash',$baseurl=''){
    $html_urls = array();
    if (empty($urls) || empty($selectors)) {
        printf("%s\n",'Argument Error: url and selector missed!');
        return false;
    }

    if (empty($tabnames)) {
        $html_urls = $urls;
    }
    if (is_string($urls) && is_string($tabnames)) {
        $html_urls[$urls]['index']=$urls;
        if ($tabtype=='slash') {
            $html_urls[$urls][$tabnames] = empty($baseurl)?$urls.'/'.$tabnames:$baseurl.'/'.$tabnames;
        }else if ($tabtype == 'query'){
            $html_urls[$urls][$tabnames] = empty($baseurl)?$urls.'?'.$tabnames:$baseurl.'/'.$tabnames;
        }
    }
    if(is_string($urls) && is_array($tabnames)) {
        $html_urls[$urls]['index']= $urls;
        foreach($tabnames as $tab) {
            if ($tabtype == 'slash') {
                $html_urls[$urls][$tab] = empty($baseurl) ? $urls . '/' . $tab : $baseurl . '/' . $tab;
            } else if ($tabtype == 'query') {
                $html_urls[$urls][$tab] = empty($baseurl) ? $urls . '?' . $tab : $baseurl . '/' . $tab;
            }
        }
    }

    if (is_array($urls) && is_string($tabnames)) {
        foreach($urls as $url) {
            $html_urls[$url]['index'] = $url;
            if ($tabtype == 'slash') {
                    $html_urls[$url][$tabnames] = empty($baseurl) ? $url . '/' . $tabnames : $baseurl . '/' . $tabnames;
                } else if ($tabtype == 'query') {
                    $html_urls[$url][$tabnames] = empty($baseurl) ? $url . '?' . $tabnames : $baseurl . '/' . $tabnames;
                }
            }
    }

    if (is_array($urls) && is_array($tabnames)) {
        foreach($urls as $url) {
            $html_urls[$url]['index'] = $url;
            foreach ($tabnames as $tab) {
                if ($tabtype == 'slash') {
                    $html_urls[$url][$tab] = empty($baseurl) ? $url . '/' . $tab : $baseurl . '/' . $tab;
                } else if ($tabtype == 'query') {
                    $html_urls[$url][$tab] = empty($baseurl) ? $url . '?' . $tab : $baseurl . '/' . $tab;
                }
            }
        }
    }
    $flash_tag_urls = array();
    foreach($html_urls as $url_name => $html_url) {
        foreach ($html_url as $tab_name => $tab_url) {
            \requests::$output_encoding='UTF-8';
            \requests::$input_encoding='UTF-8';
            $flash_tag_html = \requests::get($tab_url);
            $tmp_tag_urls = array();
            if (is_string($selectors)) {
                $sub_tmp_tag_urls = \selector::select($flash_tag_html,$selectors);
                if (is_string($sub_tmp_tag_urls)) {
                    $tmp_tag_urls[] = $sub_tmp_tag_urls;
                } else if (is_array($sub_tmp_tag_urls)) {
                    $tmp_tag_urls = array_merge($tmp_tag_urls,$sub_tmp_tag_urls);
                }
            }
            if (is_array($selectors)) {
                $sub_selectors = @$selectors[$url_name];
                if (empty($sub_selectors)) {
                    continue;
                }
                if (is_string($sub_selectors)) {
                    $sub_tmp_tag_urls = \selector::select($flash_tag_html, $sub_selectors);
                    if (is_string($sub_tmp_tag_urls)) {
                        $tmp_tag_urls[] = $sub_tmp_tag_urls;
                    } else if (is_array($sub_tmp_tag_urls)) {
                        $tmp_tag_urls = array_merge($tmp_tag_urls,$sub_tmp_tag_urls);
                    }
                }
                if (is_array($sub_selectors)) {
                    $tmp_tag_urls = array();
                    $third_selectors = @$sub_selectors[$tab_name];
                    if (empty($third_selectors)) {
                        continue;
                    }
                    if (is_string($third_selectors)){
                        $sub_tmp_tag_urls = \selector::select($flash_tag_html,$third_selectors);
                        if (is_string($sub_tmp_tag_urls)) {
                            $tmp_tag_urls[] = $sub_tmp_tag_urls;
                        } else if (is_array($sub_tmp_tag_urls)) {
                            $tmp_tag_urls = array_merge($tmp_tag_urls,$sub_tmp_tag_urls);
                        }

                    }
                    if (is_array($third_selectors)) {
                        foreach ($third_selectors as $selector) {
                            $sub_tmp_tag_urls = \selector::select($flash_tag_html, $selector);
                            if (!empty($sub_tmp_tag_urls)) {
                                if (is_string($sub_tmp_tag_urls)) {
                                    $tmp_tag_urls[] = $sub_tmp_tag_urls;
                                } else if(is_array($sub_tmp_tag_urls)) {
                                    $tmp_tag_urls = array_merge($sub_tmp_tag_urls,$tmp_tag_urls);
                                }
                            }

                        }
                    }

                }
            }
            if (!empty($tmp_tag_urls)) {
                $flash_tag_urls = array_merge($flash_tag_urls, $tmp_tag_urls);
            }

        }


    }
    return $flash_tag_urls;
}
}
