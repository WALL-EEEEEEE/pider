<?php
namespace Module\Data\GrapeWine;

use Module\Data\ActivedCarbon;
use Module\Data\Pore;

class GrapeWineActivedCarbon extends ActivedCarbon {


    /**
     * Define several pores 
     */
    protected function selfPores():array {
        $pores = [];
        $pores[] = new GrapeWineCountryPore();
        $pores[] = new GrapeCategoryPore();
        $pores[] = new GrapeWineCategoryPore();
        $pores[] = new GrapeWinePackagePore();
        return $pores;
    }
}

