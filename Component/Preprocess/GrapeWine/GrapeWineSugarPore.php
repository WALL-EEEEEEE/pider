<?php
namespace Preprocess\GrapeWine;
use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;

class GrapeWineSugarPore extends Pore {
    private $ikeywords = [
        '甜度',
        '糖分'
    ];
    private $vkeywords = [
        '干' =>'干型' ,
        '半干'=>'半干型',
        '半甜'=> '半甜型',
        '甜' => '甜型',
    ];
    
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
                $p_name_regex = '/'.implode($this->ikeywords,'|').'/i';
                if (preg_match($p_name_regex,$p_name) || array_key_exists($p_value,$this->self_datas) || in_array($p_value,$this->self_datas)) {
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
                } else if (count($data) >= 1){
                    var_dump($data);
                    foreach($data as $key => $value) {
                        if ($value == '其他') {
                            $clean_data['sugar_ch'] = '';
                            $clean_data['sugar_en'] = '';
                        } else {
                            $vkeywords = $pore->getVkeywords();
                            array_walk($vkeywords,function(&$v,$k) use(&$value) {
                                $regex = '/'.$k.'/i';
                                if(preg_match($regex,$value)) {
                                    $value = $v;
                                }
                            });
                            if (array_key_exists($value,$pore->self_datas)) {
                                $clean_data['sugar_ch']  = $value;
                                $clean_data['sugar_en']  = $pore->self_datas[$value];
                            } else if (in_array($value,$pore->self_datas)) {
                                $reverse_self_datas = array_flip($pore->self_datas);
                                $clean_data['sugar_en']  = $value;
                                $clean_data['sugar_ch']  = $reverse_self_datas[$value];
                            }
                       }
                    }
                } else {
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }

    public function getVkeywords() {
        return $this->vkeywords;
    }
}

