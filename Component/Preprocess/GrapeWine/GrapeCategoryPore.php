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
                } else {
                    $grape_variety_ch = [];
                    $grape_variety_en = [];
                    foreach($data as $key => $value) {
                        $value = trim($value);
                        foreach($pore->self_datas as $k => $v) {
                            $regex = '/('.addcslashes($k,'()/').'|'.addcslashes($v,'()/').')/i';
                            if (preg_match($regex,$value)) {
                                $grape_variety_ch[] = $k; 
                                $grape_variety_en[] = $v;
                            }
                        }
                    }
                    $grape_variety_ch = array_unique($grape_variety_ch);
                    $grape_variety_en = array_unique($grape_variety_en);
                    $clean_data['grape_variety_ch'] = implode($grape_variety_ch,',');
                    $clean_data['grape_variety_en'] = implode($grape_variety_en,',');
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}

