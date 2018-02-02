<?php
namespace Preprocess\GrapeWine;
use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;

class GrapeCategoryPore extends Pore {
    
    protected function selfFeatures():array {
        $this->self_datas = (new GrapeCategoryDI())();
        $GrapeCategoryPropertyExist = new Throttle(function($data) {
            $subdata = [];
            foreach($data as $p_name => $p_value ) {
                if (preg_match('/葡萄品种/i',$p_name) || in_array($p_value,$this->self_datas) || array_key_exists($p_value,$this->self_datas)) {
                    $subdata[$p_name] = $p_value;
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($GrapeCategoryPropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['grape_variety_ch'] = '';
                    $clean_data['grape_variety_en'] = '';
                } else if (count($data) == 1){
                    foreach($data as $key => $value) {
                        if(in_array($value,$pore->self_datas) || array_key_exists($value,$pore->self_datas)) {
                            $clean_data['grape_variety_ch'] = $value; 
                            $clean_data['grape_variety_en']  = $pore->self_datas[$value];
                        }
                        $clean_data['grape_variety_ch'] = ''; 
                        $clean_data['grape_variety_en']  = '';
                    }
                } else {
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}

