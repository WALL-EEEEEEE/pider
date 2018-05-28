<?php
namespace Preprocess\GrapeWine;

use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;

class GrapeWineCapacityPore extends Pore {
    private $ikeywords = [
        '规格',
        '容量',
        '净含量'
    ];
    private $vkeywords = [
        'L',
        'ML'
    ];
    
    protected function selfFeatures():array {
       $WineColorPropertyExist = new Throttle(function($data) {
            $subdata = [];
            $ikeywords_regex = '/'.implode($this->ikeywords,'|').'/i';
            $vkeywords_regex = '/'.implode($this->vkeywords,'|').'/i';
            foreach($data as $p_name => $p_value ) {
                if (preg_match($ikeywords_regex,$p_name) && preg_match($vkeywords_regex,$p_value)) {
                    $subdata[$p_name] = $p_value;
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($WineColorPropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['capacity'] = '';
                } else {
                    foreach($data as $key => $value) {
                        $capacity_num_regex = '/(?<number>\d+)(^'.implode($pore->getvKeywords(),'|').')*(?<unit>'.implode($pore->getvKeywords(),'|').')/i';
                        $if_match = preg_match($capacity_num_regex,$value,$capacity_num);
                        if ($if_match && !empty($capacity_num['number']) && !empty($capacity_num['unit'])) {
                            $clean_data['capacity'] = $capacity_num['number'].strtoupper($capacity_num['unit']);
                        } else {
                            $clean_data['capacity']  = trim(strtoupper($value));
                        }
                    }
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }

    public function getvKeywords() {
        return $this->vkeywords;
    }

    public function getiKeywords() {
        return $this->ikeywords;
    }
}

