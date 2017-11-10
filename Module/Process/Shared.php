<?php
namespace Module\Process;
/**
 * @class Shared 
 * This function is a briage for shareing data between processes
 */

class Shared {
    private $data;

    /**
     * Init a shared memory block, and push the shared data into block;
     *
     */
    public function __init() {
        $pid = getmypid();
        $shm_key = ftok(__FILE__,chr($pid));
        //count the size of shared data
        $block_size = mb_strlen(serialize($this->data));
        //because the key's length used by shmop_open() must >= 9,so just warrant the length of its
        if (strlen($shm_key) < 9) {
            $shm_key = str_pad($shm_key,9,"0");
        }
        //allocate enough size for sharedmemory block
        $shm_id = shmop_open($shm_key,"c",0644,$size);
        shmop_write($shm_id,serialize($this->data),0);
    }
}
