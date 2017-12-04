<?php
namespace Module\Data\GrapeWine;
use Module\Data\Pore;
use Module\Data\Rule;
use Module\Data\Throttle;
use Module\Data\Reaction;


class GrapeWineColorPore extends Pore {
    
    protected function selfFeatures():array {
       $WineColorPropertyExist = new Throttle(function($data) {
            $subdata = [];
            foreach($data as $p_name => $p_value ) {
                if (preg_match('/颜色/i',$p_name)) {
                    $subdata[$p_name] = $p_value;
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($WineColorPropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['color_ch'] = '';
                    $clean_data['color_en'] = '';
                } else if (count($data) == 1){
                    foreach($data as $key => $value) {
                        $clean_data['color_ch']  = $value;
                        $clean_data['color_en']  = '';
                    }
                } else {
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}

