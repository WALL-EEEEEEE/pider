<?php
namespace Pider\Kernel\Process;
/**
 * @class Shared 
 * This function is a briage for shareing data between processes
 */

class Shared {
    private static $data;

    public function __construct(array $data) {
        if (empty($data)) {
            throw new \InvalidArgumentException("Arguement #1, must be non-empty array!");
        }
        self::$data = $data;
    }
 
    /**
     * Init a shared memory block, and push the shared data into block;
     */
    public static function store($shared_key, $data = [])  {
        if (empty($shared_key) || !is_numeric($shared_key)){
            throw new \InvalidArgumentException("Arguement #1 must be an non-numberic string.");
        }
        if (!empty($data)) {
            self::$data = $data;
        }
        //count the size of shared data
        $block_size = mb_strlen(serialize(self::$data));
        //because the key's length used by shmop_open() must >= 9,so just warrant the length of its
        $shm_key = self::key($shared_key);
        //allocate enough size for sharedmemory block
        $shm_id = shmop_open($shm_key,"c",0644,$block_size);
        shmop_write($shm_id,serialize(self::$data),0);
        shmop_close($shm_id);
    }

    public static  function restore($shared_key) {
        if (empty($shared_key) || !is_numeric($shared_key)){
            throw new \InvalidArgumentException("Arguement #1 must be an non-numberic string.");
        }
        $shm_key = self::key($shared_key);
        $shm_id = @shmop_open($shm_key,'a',0,0);
        if ($shm_id) {
            $block_size = shmop_size($shm_id);
            $org_data = shmop_read($shm_id,0,$block_size);
            $data = unserialize($org_data);
            shmop_close($shm_id);
            return $data;
        } else {
            return [];
        }
    }

    public static function destory($shared_key) {
        if (empty($shared_key) || !is_numeric($shared_key)){
            throw new \InvalidArgumentException("Arguement #1 must be an non-numberic string.");
        }
        $shm_key = self::key($shared_key);
        $shm_id = @shmop_open($shm_key,'a',0,0);
        if ($shm_id) {
            shmop_delete($shm_id);
            shmop_close($shm_id);
        }
    }

    private static  function key($shared_key) {
        $shm_key = ftok(__FILE__,chr($shared_key));
        //because the key's length used by shmop_open() must >= 9,so just warrant the length of its
        if (strlen($shm_key) < 9) {
            $shm_key = str_pad($shm_key,9,"0");
        }
        return $shm_key;
    }


}

