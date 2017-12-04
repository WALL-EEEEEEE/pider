<?php
namespace Module\Data\GrapeWine;
use Module\Data\Pore;
use Module\Data\Rule;
use Module\Data\Throttle;
use Module\Data\Reaction;


class GrapeWineSugarPore extends Pore {
    
    protected function selfFeatures():array {
        $this->self_datas = [
            '干型'=> 'Dry',
            '半干型'=>'Medium-dry',
            '半甜型'=>'Medium-sweet',
            '甜型'=> 'Sweet', 
            '自然干'=> 'Brut Nature/Naturherb',
            '超天然'=>'Extra Brut/Extra Herb',
            '天然'=> 'Brut/Herb',
            '极干'=> 'Extra Dry/Extra Seco/Extra Trocken'
        ];
        $WineSugarPropertyExist = new Throttle(function($data) {
            $subdata = [];
            foreach($data as $p_name => $p_value ) {
                if (preg_match('/甜度/i',$p_name) || array_key_exists($p_value,$this->self_datas) || in_array($p_value,$this->self_datas)) {
                    $subdata[$p_name] = $p_value;
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($WineSugarPropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['sugar_ch'] = '';
                    $clean_data['sugar_en'] = '';
                } else if (count($data) == 1){
                    foreach($data as $key => $value) {
                        $clean_data['sugar_ch']  = $value;
                        $clean_data['sugar_en']  = $pore->self_datas[$value];
                    }
                } else {
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}

