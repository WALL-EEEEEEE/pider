<?php

namespace Module\Data;

/**
 * @class ActivedCarbon 
 *
 * Cleaning data like actived carbon through absorbing and reacting. 
 *
 */

class ActivedCarbon {

    /**
     * @attribute Pore list to store all pores
     */
    private $pores = [];
    /**
     * @attribute Dirty data to be purified
     */
    private $dirty_datas = [];

    public function __construct(array $dirty_datas) {
        $this->dirty_datas = $dirty_datas;
    }
    /**
     * @method addPore(Pore) 
     * Add a pore
     */
    public function addPore(Pore $pore) {
        $this->$pores[$pore->getPoreId] = $pore;
    }

    /**
     * @method delPore(Pore)
     * @return if successful , a pore id is returned
     * Delete a pore if it exists
     */
    public function delPore(Pore $pore) {
        $pore_id = $pore->getPoreId();
        if (array_key_exists($pore_id,$this->pores)) {
            unset($this->pores[$pore_id]);
            return $pore_id;
        }
    }

    /**
     * @method purify()
     *
     * Purify the dirty data
     *
     */
    public function purify() {
        if (count($this->dirty_datas) >= 1 ) {
            foreach($this->pores as $pore) {
                $pore->...
            }
        }
    }
}


