<?php
namespace Preprocess\GrapeWine;
use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;

class GrapeWineRecipePore extends Pore {
    private $ikeywords = [
        '搭配菜肴',
        '菜肴',
    ];
    
    protected function selfFeatures():array {
        $GrapeWineRecipePropertyExist = new Throttle(function($data) {
            $subdata = [];
            foreach($data as $p_name => $p_value ) {
                $regex = '/('.implode($this->ikeywords,'|').')/i';
                if (preg_match($regex,$p_name)) {
                    $subdata[$p_name] = $p_value;
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($GrapeWineRecipePropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['recipe_pair_ch'] = '';
                    $clean_data['recipe_pair_en'] = '';
                } else {
                    $recipe_pair_ch = [];
                    foreach($data as $key => $value) {
                        $value = trim($value);
                        $recipe_pair_ch[] = $value; 
                    }
                    $recipe_pair_ch= array_unique($recipe_pair_ch);
                    $clean_data['recipe_pair_ch'] = implode($recipe_pair_ch,',');
                    $clean_data['recipe_pair_en'] = '';
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}

