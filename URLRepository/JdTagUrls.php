<?php
use Pider\Support\URLSource;
use Illuminate\Database\Capsule\Manager as Capsule;

class JdTagUrls implements URLSource {

    public function suck() {
       $skus  =   Capsule::table('basic_retailers','standard')->where('ec_platform_id','=','27')->get(['ec_unique_id'])->toArray();
       $urls  = array_map(function($value) {
            $sku = (string)$value->ec_unique_id;
            return 'https://item.jd.com/'.$sku.'.html';
       },$skus);
       $urls= array_unique($urls);
       return $urls;
    }
}
