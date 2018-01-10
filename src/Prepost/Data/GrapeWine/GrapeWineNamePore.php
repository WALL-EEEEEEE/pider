<?php
namespace Module\Data\GrapeWine;
use Module\Data\Pore;
use Module\Data\Rule;
use Module\Data\Throttle;
use Module\Data\Reaction;
use Module\Data\Filter;
use Module\Data\DI\GrapeCategoryDI;

class GrapeWineNamePore extends Pore {
    
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
                $interferons = ['刀','酒具','酒架','酒柜','杯','酒瓶','酒桶','酒坛','酒托','衣','摆件','酒罐','酒精计',
                '鞋','汽车','礼品袋','醒酒器','倒酒片','礼袋','包装盒'];
                $assoc = ['\+','送','赠'];
                $keyword = ['葡萄酒','红酒'];

                $iterferons_items = '';
                foreach($interferons as $item) {
                    $iterferons_items.=$item.'|';
                }
                $iterferons_items = rtrim($iterferons_items,'|');
                $iterferons_pattern = '/('.$iterferons_items.')/i';
                $assoc_items = '';
                foreach($assoc as $item) {
                    $assoc_items.=$item.'|';
                }
                $assoc_items = rtrim($assoc_items,'|');
                $assoc_pattern = '/('.$assoc_items.')/i';
                if (!empty($data)) {
                    $not_grape_wine = preg_match($iterferons_pattern,$data[0]) && !preg_match($assoc_pattern,$data[0]);
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
}

