<?php
namespace Preprocess\GrapeWine;

use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;

class GrapeWineOccasionPore extends Pore {
    
    protected function selfFeatures():array {
       $WineColorPropertyExist = new Throttle(function($data) {
            $subdata = [];
            foreach($data as $p_name => $p_value ) {
                if (preg_match('/场合/i',$p_name)) {
                    $subdata[$p_name] = $p_value;
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($WineColorPropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['occasion_ch'] = '';
                    $clean_data['occasion_en'] = '';
                } else {
                    foreach($data as $key => $value) {
                        $clean_data['occasion_ch']  = trim($value);
                        $clean_data['occasion_en']  = '';
                    }
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}

