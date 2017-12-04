<?php
namespace Module\Data\GrapeWine;

use Module\Data\ActivedCarbon;
use Module\Data\Pore;

class GrapeWineActivedCarbon extends ActivedCarbon {


    /**
     * Define several pores 
     */
    protected function selfPores():array {
        $pores = [
            new GrapeWineCountryPore(),
            new GrapeCategoryPore(),
            new GrapeWineCategoryPore(),
            new GrapeWineSugarPore(),
            new GrapeWineColorPore(),
            new GrapeWinePackagePore(),
        ];
        return $pores;
    }
}

