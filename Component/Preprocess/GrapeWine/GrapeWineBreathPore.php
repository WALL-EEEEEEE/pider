<?php
namespace Preprocess\GrapeWine;

use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;

class GrapeWineBreathPore extends Pore {
    private $ikeywords = [
        '醒酒',
    ];
    private $vkeywords = [
        '分钟',
        '分',
        '秒',
        '小时',
        '时',
        'min',
        'm',
        'minute',
        's',
        'hour',
        'h'
    ];
    
    protected function selfFeatures():array {
       $WineBreathPropertyExist = new Throttle(function($data) {
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

        $GragulateReaction = new class($WineBreathPropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['breathing'] = '';
                } else {
                    foreach($data as $key => $value) {
                        $breath_regex = '/(?<number>\d+)(^'.implode($pore->getvKeywords(),'|').')*(?<unit>'.implode($pore->getvKeywords(),'|').')/i';
                        $if_match = preg_match($breath_regex,$value,$breath);
                        if ($if_match && !empty($breath['number']) && !empty($breath['unit'])) {
                            $clean_data['breathing'] = $breath['number'].strtoupper($breath['unit']);
                        } else {
                            $clean_data['breathing']  = trim(strtoupper($value));
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

