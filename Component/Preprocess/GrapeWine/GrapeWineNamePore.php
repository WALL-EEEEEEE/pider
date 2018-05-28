<?php
namespace Preprocess\GrapeWine;
use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;
use Pider\Prepost\Data\Filter;

class GrapeWineNamePore extends Pore {
    private  $vinterferons = [
        '刀','酒具','酒架','酒柜','杯','酒瓶','酒桶','酒坛','酒托','衣','摆件','酒罐',
        '酒精计','鞋','汽车','礼品袋','醒酒器','倒酒片','礼袋','包装盒','补差价','补邮',
        '定制链接专拍','手提袋','不含酒','红葡萄汁','石榴酒','山楂酒','苹果酒','蓝莓酒',
        '玫瑰花酒','水果酒','露酒','水果发酵','枸杞酒','梨酒','蓝莓酒','桂花酒','桂花老酒',
        '配制酒','赠品勿拍','桂花陈酒','蓝莓冰酒'
    ];
    private $vassocs = ['\+','送','赠'];
    private $vkeywords = ['葡萄酒','红酒'];
    
    protected function selfFeatures():array {
        $this->self_datas = (new GrapeCategoryDI())();
        $GrapeWineNameExistsRule = new Throttle(function($data){
            $product_name = array_key_exists('商品名称',$data)?$data['商品名称']:'';
            $product_name = (array_key_exists('商品名',$data) && empty($product_name))?$data['商品名']:$product_name;
            $product_name = (array_key_exists('名称',$data) && empty($product_name))?$data['名称']:$product_name;
            if (!empty($product_name)) {
                return [$product_name];
            }
            return []; 
        });

       $NotGrapeWineNameFilter = new class($GrapeWineNameExistsRule) extends Filter {
            public function filter(array $data, Pore $pore) {
                $interferons = $pore->getVinterferons();
                $assoc = $pore->getVassoc();
                $keywords = $pore->getVkeywords();
                $interferons_items = '';
                foreach($interferons as $item) {
                    $interferons_items.=$item.'|';
                }
                $interferons_items = rtrim($interferons_items,'|');
                $interferons_pattern = '/('.$interferons_items.')/i';
                $assoc_items = '';
                foreach($assoc as $item) {
                    $assoc_items.=$item.'|';
                }
                $assoc_items = rtrim($assoc_items,'|');
                $assoc_pattern = '/('.$assoc_items.')/i';
                if (!empty($data)) {
                    $not_grape_wine = preg_match($interferons_pattern,$data[0]) && !preg_match($assoc_pattern,$data[0]);
                    if ($not_grape_wine) {
                        return false;
                    }
                    return true;
                } 
                return false;
            }
        };
        $GrapeWineNameThrottle = new Throttle(function($data) {
            return [];
        });

        $GragulateReaction = new class($GrapeWineNameThrottle) extends Reaction{
            public function react(array $data, Pore $pore):array {
                return [];
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[$NotGrapeWineNameFilter],'absorber'=>[]];
    }

    public function getVinterferons() {
        return $this->vinterferons;
    }
    public function getVkeywords() {
        return $this->vkeywords;
    }
    public function getVassoc() {
        return $this->vassocs;
    }
}
