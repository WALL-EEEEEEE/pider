<?php
include_once('../app.php');

use Module\Pider;
use Module\Http\Response;
use Module\Data\GrapeWine\GrapeWineActivedCarbon;
use Module\Data\Pore;
use Util\Api;
use Extension\DBExtension;


DBExtension::switch_db('jhbian_spider');
$GLOBALS['website']['id'] = 1;
class JdWineDetails extends Pider {

    protected $domains = [
        'www.jd.com'
    ];

    public function start_requests():array {
        $std_urls = [];
        $crawler_urls = [];
        //Load the url infomation from standard database
        $std_urls = Api::get_standard_products_url(10000,'jd.com');
        //Load crawler_urls from crawle database
        $raw_crawler_urls = DBExtension::get_all("select all_html.url as url from all_html where website_id = ".$GLOBALS['website']['id']);
        foreach($raw_crawler_urls as $url_arr ) {
            $crawler_urls[] = $url_arr['url'];
        }
        $filter_urls = array_diff($crawler_urls,$std_urls);
        return $filter_urls;
    }

    public function parse(Response $response) {
        $response = $response->outputEncode('utf-8');
        $wine_details = $response->xpath('//div[contains(@class,"p-parameter")]/ul/li')->extract();
        foreach($wine_details as $key => $detail) {
            $split_detail = explode('：',$detail);
            $detail_name = trim($split_detail[0]);
            $detail_value = trim($split_detail[1]);
            unset($wine_details[$key]);
            $wine_details[$detail_name] = $detail_value;
        }

        $clean_wine_details = (new GrapeWineActivedCarbon($wine_details))();
        $clean_wine_details = array_diff($clean_wine_details,$wine_details);
        var_dump($clean_wine_details);
        exit(0);
    }
}

