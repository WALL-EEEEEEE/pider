<?php
namespace Preprocess\GrapeWine;
use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;


class GrapeWinePackagePore extends Pore {
    
    protected function selfFeatures():array {
        $this->self_datas = [
           '瓶装', 
           '箱装',
        ];
        $WinePackagePropertyExist = new Throttle(function($data) {
            $subdata = [];
            foreach($data as $p_name => $p_value ) {
                if (preg_match('/包装/i',$p_name) || in_array($p_value,$this->self_datas)) {
                    $subdata[$p_name] = $p_value;
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($WinePackagePropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['packaging_ch'] = '';
                    $clean_data['packaging_en'] = '';
                } else if (count($data) == 1){
                    foreach($data as $key => $value) {
                        if (preg_match('/箱/i',$value)) {
                            $clean_data['packaging_ch']  = '箱装';
                        } else if (preg_match('/瓶/i',$value)) {
                            $clean_data['packaging_ch'] = '瓶装'; 
                        } else {
                            $clean_data['packaging_ch'] = '';
                        }
                        
                        $clean_data['packaging_en']  = '';
                    }
                } else {
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}

