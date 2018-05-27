<?php
namespace Util\StructHtml;
/**
 * class SearchEntry
 *
 * This class is a helper tool that can process structed html with search-entry well.
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/9/7
 * Time: 11:32
 */

class SearchEntry
{
    //concatenation type of  page url
    const PAGE_URL_SLASH = 0 ; //as http://www.xxx.com/:pnum:
    const PAGE_URL_QUERY = 1;  //as http://www.xxx.com/...?...&pnum
    //default to PAGE_URL_QUERY
    private $page_url_type = 1;
    private $input_encode = 'UTF-8';
    private $output_encode = "UTF_8";
    private $search_return  = '';
    private $extra_param = '';
    private $tabs_selector = '';
    private $page_selector = '';
    private $totalpages_selector = '';
    private $results_box_selector = '';
    private $if_iterate = false;
    private $page_url = '';
    private $page_param = '';
    private $iterate_callback = null;
    private $external_data = array() ;
    private $search_query = '';
    private $final_result = '';
    private $total_pages = 0;
    private $current_page = 0;
    private $pages = array();
    private $tabs = array();
    private $resultbox =  '';
    private $skip_pages = array();
    private $allow_skip_page_type = array(
        'odd',
        'even'
    );
    public $entry = null;
    public $entry_type ='';
    public $keyword = null;
    public $keyword_param = '';
    public $result_selector = '';
    /**
     * searchentry constructor.
     * use a url or a html element contains the search input to construct a searchentry
     * @param $entry
     */
   public function __construct($entry) {
       $this->entry = $entry;
       $this->detect_entry();
   }

    /**
     * set extra url query-params for url entry of search
     * @param $extra_param
     * @return $this
     */
   public function extra_param($extra_param) {
       if (!empty($extra_param)) {
           $this->extra_param = $extra_param;
       }
       return $this;
   }


  /**
    * set keyword-param query string for url entry of search,default to "keyword"
    *
    * */
  public function keyword_param($keyword_param) {
      if (!empty($keyword_param)) {
          $this->keyword_param = $keyword_param;
      }
      return $this;
  }

    /**
     * set page-param query string for url entry of search,default to "page"
     *
     */
    public function page_param($page_param) {
        if (!empty($page_param)) {
            $this->page_param = $page_param;
        }
        return $this;
    }

    /**
     * set tab selector for searcher tab switches
     * searchentry class use this selector to pinpoint tabs for filtering search results
     * @param $selector
     * @return $this
     */
   public function tabs($selector) {
       if (!empty($selector)) {
           $this->validate_xpath($selector);
           $this->tabs_selector = $selector;
       }
       return $this;
   }

    /**
     * allow external data transfered in
     *
     * External data will be merged with searcher collected and proceeded together
     */
   public function extern($data) {
       if (!empty($data)) {
           if (is_string($data)){
               $this->external_data[] = $data;
           } else if (is_array($data)) {
               $this->external_data = empty($this->external_data)?$data:array_merge($this->external_data,$data);
           } else {
               throw new \ErrorException('Invalid external data');
           }
       }
   }
 
   /**
    * return current page, if current page is skipped return -1
    */
   public function get_current_page() {
       return $this->current_page;
   }

   /**
    * allow specify server page no to be skipped 
    * @param mixed $pages This may be a string ('odd','even')   or just an array contains page-numbers, if the skip page is larger than total page numbers, ignorred, this function must be called after totoalpages() method
    */
   public function skip($pages,$start=0,$end=0) {
      if(empty($this->total_pages) || $this->total_pages < 1) {
          throw new \ErrorException("Invalid  page volume,please ensure skip() method is called after totalpages() and search() methods!");
      }
      if (!empty($start)  && ($start < 0 || $start > $this->total_pages)) {
          throw new \ErrorException("Invalid start index");
      }
      if (!empty($end) && ($end < 0 || $start > $this->totalpages)) {
          throw new \ErrorException("Invalid end index");
      }
      if (empty($pages)) {
           throw new \ErrorException("Empty page numbers");
       }
       if (is_numeric($pages) && $pages >= 0 && $pages <= $this->total_pages) {
           $this->skip_pages = $pages;
           return $this;
       }
       if (is_pos_array($pages)){
           $this->skip_pages = $pages;
           return $this;
       }
       if (is_string($pages) && in_array($pages,$this->allow_skip_page_type)) {
           $tmparray = array();
           if ($start >= 1 && $end >= 1) {
               $tmparray = narrays($start,$this->end);
           } else if ($start >= 1 ) {
               $tmparray = narrays($start,$this->total_pages);
           } else if ($end >= 1) {
               $tmparray = narrays(1,$this->end);
           } else {
               $tmparray = narrays(1,$this->total_pages);
           }
           switch($pages) {
           case 'odd':
               $this->skip_pages = odd_array($tmparray); 
               return $this;
               break;
           case 'even':
               $this->skip_pages = even_array($tmparray);
               return $this;
               break;
           default:
               break;
           }
       }

       throw new \ErrorException("Invalid skip pages!");
   }

    /**
     *  set results box selector
     */
    public function resultsbox($selector) {
        if (!empty($selector)) {
            $this->validate_xpath($selector);
            $this->results_box_selector= $selector;
       }
       return $this;
    }

    /**
     * set pagination  selector
     * searchentry class use this selector to pinpoint pagination's location
     * @param $selector
     * @return $this
     */
   public function page($selector,$url = '') {
       if (!empty($selector)) {
           $this->validate_xpath($selector);
           $this->page_selector = $selector;
       }
       if (!empty($url)) {
           $this->page_url = $url;
       }
       return $this;
   }

    /**
     * set total pages selector
     * searchentry class use this selector to get the total number of pages;
     * @param string|number it can be a selector string or page volume number
     */
   public function totalpages($totalpage, $url ='' ) {
       if (empty($totalpage)) {
           throw new ErrorException("Argument: a number or a selector string should be passed.");
       }
       if (is_numeric($totalpage)) {
          $this->total_pages = intval($totalpage);
       }
       if (is_string($totalpage)) {
            $this->validate_xpath($totalpage);
            $this->totalpages_selector = $totalpage;
       }
       if (!empty($url)) {
           $this->page_url = $url;
       }
       return $this;
   }


   /**
    *
    *set page volume 
    *
    */
   public function reset_totalpages($volume,$algorithm='') {
       $algo_operand = array(
           '*','+','-','/'
       );
       if (empty($volume) || !is_numeric($volume) || $volume < 1) {
           throw new \ErrorException("Invalid page volume!");
       }
       if (empty($algorithm)) {
           $this->total_pages = $volume;
           return $this;
       }
       if (!empty($algorithm) && in_array($algorithm,$algo_operand)) {
           switch($algorithm){
           case '*':
               $this->total_pages *= $volume;
               break;
           case '/':
               $this->total_pages /= $volume;
               break;

           case '+':
               $this->total_pages -= $volume;
               break;

           case '-':
               $this->total_pages += $volume;
               break;
           }
           return $this;
       }
       throw new \ErrorException("Invalid operand");
   }

    /**
     * set if iterate all page results
     * @param  $callback callable call it each time while traversing all pages
     */
    public function iterate($callback = null) {
        $this->if_iterate = true;
        if (!is_null($callback) )  {
            if (!is_callable($callback)) {
               throw new \ErrorException('Invalid callback.');
            }
            $this->iterate_callback = $callback;
        }
        return $this;
    }

    /**
     *
     * detect what kind entry was passed
     *
     */
   private function detect_entry() {
       if (empty($this->entry)) {
           throw  new \ErrorException("Empty entry");
       }
       //detect if is url
       if (filter_var($this->entry,FILTER_VALIDATE_URL)) {
           $this->entry_type = 'url';
           //strip the query position
           $url_part = parse_url($this->entry);
           $this->entry = @$url_part['scheme'].'://'.@$url_part['host'].@$url_part['path'];
       }
       //detect if is html element
       if (\is_html($this->entry)) {
           $this->entry_type = 'html';
           $this->entry = htmlspecialchars($this->entry);
       }

       if (empty($this->entry)) {
           throw new \ErrorException("Invalid entry.");
       }

       if (empty($this->entry_type)) {
           throw new \ErrorException("Unkown entry!");
       }
   }

    /**
     * search keywords and return result  as search box
     * @param $keywords string keywords for searching
     * @return $search_result
     */
   public function search($keywords,$selector ='') {
       if (empty($keywords)) {
           return false;
       }
       if (!empty($selector)) {
           $this->validate_xpath($selector);
          $this->result_selector = $selector;
       }

       if ($this->entry_type == 'url') {
           $keywords = rawurlencode($keywords);
       }
       if ($this->entry_type == 'html') {
           $keywords = htmlspecialchars($keywords);
       }
       $this->keyword = $keywords;
       \requests::$input_encoding= $this->input_encode;
       \requests::$input_encoding = $this->output_encode;
       if (!empty($this->keyword_param)) {
          $search_query = $this->entry.'?' .$this->keyword_param.'='.$keywords;
       } else {
           $search_query = $this->entry.'?keyword='.$keywords;
       }
       if (!empty($this->extra_param)) {
           $search_query = $search_query.'&'.$this->extra_param;
       }
       $this->search_query = $search_query;
       $this->search_return = \requests::get($search_query);
       if (empty($this->total_pages)) {
           $this->detect_total_pages();
       }
       $this->detect_filter_tabs();
       $this->detect_result_box();
       return $this;
   }

   public function go() {
       //just get the first page of search results
       if (!$this->if_iterate) {
           $return = empty($this->results_box_selector)? $this->search_return:\selector::select($this->search_return,$this->results_box_selector);
           $return =empty($this->result_selector)?$return:\selector::select($return,$this->result_selector);
           if(!empty($return)) {
              return $return;
           }
       } else {
           //get all pages' results
           if ($this->total_pages < 1) {
               throw new \ErrorException("Invalid page volume!");
           }
           $search_return = array();
           //concatenate all page urls and get other pages' results
           if (!in_array(1,$this->skip_pages)) {
               $search_return[] = $this->search_return;
               $this->current_page = 1;
               if (!empty($this->iterate_callback)) {
                   $callback = $this->iterate_callback;
                   call_user_func($callback,$this);
               }
           }
           for ($i = 2; $i < $this->total_pages; $i++) {
               if (in_array($i,$this->skip_pages)) {
                   $this->current_page = -1;
                   continue;
               }
               $this->current_page = $i;
               $page_url = '';
               if (empty($this->page_url)) {
                  $this->page_url = $this->search_query;
               }
               if ($this->page_url_type == self::PAGE_URL_QUERY) {
                   if (strpos($this->page_url,'?') === false) {
                       $page_url=empty($this->page_param)?$this->page_url.'?page='.$i:$this->page_url.'?'.$this->page_param.'='.$i;
                   } else {
                       $page_url=empty($this->page_param)?$this->page_url.'&page='.$i:$this->page_url.'&'.$this->page_param.'='.$i;
                   }
               } else if ($this->page_url_type == self::PAGE_URL_SLASH ){
                   $page_url=empty($this->page_param)?$this->page_url.'/page/'.$i:$this->page_url.'/'.$this->page_param.'/'.$i;
               }

               if (!filter_var($this->page_url,FILTER_VALIDATE_URL)) {
                   throw new \ErrorException("Invalid page url!");
               }
               $search_return[$i] = \requests::get($page_url);
               if (!empty($this->iterate_callback)) {
                   call_user_func($this->iterate_callback,$this);
               }
           }

           if (empty($this->result_selector)) {
              return $search_return;
           } else {
               $return = array();
              foreach($search_return as $key => $esearch_return) {
                  $subreturn = \selector::select($esearch_return,$this->result_selector);
                  $return = empty($subreturn)?$return:array_merge($return,$subreturn);
               }

               if (!empty($this->external_data)) {
                  $return=array_merge($return,$this->external_data);
               }
 
               if (!empty($return)) {
                   return  $return;
               }
           }
       }
       return false;
   }

   private function detect_total_pages() {
     if (!empty($this->totalpages_selector)) {
         $pages = \selector::select($this->search_return,$this->totalpages_selector);
         if (!strpos('/',$pages)) {
             $pages = intval($pages);
         } else {
             $pages = intval(trim($pages,'/'));
         }
         $this->total_pages = $pages;
     }
   }

    /**
     * @param $selector string xpath selector or regex match filter_tabs element
     */
   private function detect_filter_tabs() {
       if (!empty($this->tabs_selector)) {
           $tabs = \selector::select($this->search_return,$this->tabs_selector);
           $this->tabs = $tabs;
       }
   }


   private function detect_result_box() {
        if (!empty($this->results_box_selector)) {
            $this->results_box = \selector::select($this->search_return,$this->results_box_selector);
        }
   }

   private function validate_xpath($selector) {
       if (!\validate_xpath($selector)) {
          throw new \ErrorException("Invalid xpath");
       }
   }

    /**
     * automate detect filter tabs position and extract tabs' name
     */
   private function autodetect_result_box() {

   }

   /**
   * automate detect filter tabs position and extract tabs' name
   */
   private function autodetect_filter_tabs() {

   }

   public function __destruct()
   {
       // TODO: Implement __destruct() method.
       unset($this->external_data);
   }
}
