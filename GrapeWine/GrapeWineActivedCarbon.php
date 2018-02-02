<?php
namespace GrapeWine;

use Pider\Prepost\Data\ActivedCarbon;
use Pider\Prepost\Data\Pore;

class GrapeWineActivedCarbon extends ActivedCarbon {

    /**
     * Define several pores 
     */
    protected function selfPores():array {
        $pores = [
            new GrapeWineNamePore(),
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

