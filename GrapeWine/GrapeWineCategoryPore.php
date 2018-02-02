<?php
namespace GrapeWine;
use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;


class GrapeWineCategoryPore extends Pore {
    
    protected function selfFeatures():array {
        $this->self_datas = [
            '干邑'=>'Cognac',
            '红葡萄酒'=>'Red Wine',
            '白葡萄酒'=>'White Wine',
            '起泡酒'=> 'Sparkling Wine',
            '雅文邑'=> 'Armagnac',
            '桃红葡萄酒'=>'Rose Wine',
            '加强型葡萄酒'=>'Fortified Wine',
            '黄葡萄酒' => 'Vin Jaune',
            '白兰地' => 'Brandy',
            '甜葡萄酒'=>'Sweet Wine',
            '格拉帕酒'=>'Grappa'
        ];
        $WineCategoryPropertyExist = new Throttle(function($data) {
            $subdata = [];
            foreach($data as $p_name => $p_value ) {
                if (preg_match('/葡萄酒类型/i',$p_name) || array_key_exists($p_value,$this->self_datas) || in_array($p_value,$this->self_datas)) {
                    $subdata[$p_name] = $p_value;
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($WineCategoryPropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['type_ch'] = '';
                    $clean_data['type_en'] = '';
                } else if (count($data) == 1){
                    foreach($data as $key => $value) {
                        $clean_data['type_ch']  = $value;
                        $clean_data['type_en']  = $pore->self_datas[$value];
                    }
                } else {
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}

