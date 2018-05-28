<?php
namespace Preprocess\GrapeWine;
use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;

class GrapeWineAromaPore extends Pore {
    private $ikeywords = [
        '香味',
        '香气',
        '气味'
    ];
    
    protected function selfFeatures():array {
        $GrapeWineAromaPropertyExist = new Throttle(function($data) {
            $subdata = [];
            foreach($data as $p_name => $p_value ) {
                $regex = '/('.implode($this->ikeywords,'|').')/i';
                if (preg_match($regex,$p_name)) {
                    $subdata[$p_name] = $p_value;
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($GrapeWineAromaPropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['aroma_ch'] = '';
                    $clean_data['aroma_en'] = '';
                } else {
                    $aroma_ch = [];
                    foreach($data as $key => $value) {
                        $value = trim($value);
                        $aroma_ch[] = $value; 
                    }
                    $aroma_ch = array_unique($aroma_ch);
                    $clean_data['aroma_ch'] = implode($aroma_ch,',');
                    $clean_data['aroma_en'] = '';
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}

