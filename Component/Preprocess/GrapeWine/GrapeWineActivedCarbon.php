<?php
namespace Preprocess\GrapeWine;

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
            new GrapeWineOccasionPore(),
            new GrapeWineCapacityPore(),
            new GrapeWineBreathPore(),
            new GrapeWineAromaPore(),
            new GrapeWineRecipePore()
        ];
        return $pores;
    }
}

