<?php 
namespace Pider\Schedule;

use Pider\Kernel\WithKernel;
use Pider\Kernel\WithStream;
use Pider\Kernel\Stream;

class  RequestSchedule extends WithKernel implements Schedule{
    private $request_pool = [];
    /**
     * @method add()
     * Add task to Schedule 
     */
    public function add($item) {

    }

    /**
     * @method schedule()
     * Schedule tasks and Generate a priority task lists
     */
    public function schedule() {

    }

    /**
     * @method run()
     * Start the schedule process
     */
    public function run(){

    }

    /**
     * @method fromStream()
     * 
     */
    public function fromStream(Stream $stream, WithStream $kernel) {
        $request_schdule = '';
        $if_exist = $kernel->RequestSchedule;
        if (empty($if_exist)) {
            $kernel->RequestSchedule = new RequestSchedule();
        } 
        $request_schedule = $kernel->RequestSchedule;
        $request_schedule->request_pool[] = $stream->body();
    }

    /**
     * @method toStream()
     *
     */
    public function toStream() {
    }

    /** 
     * @method isStream()
     * Limit the stream to Request 
     * */
    public function isStream(Stream $stream) {
        return parent::isStream($stream)?($stream->type() == "REQUEST"?true:false):false;
    }
}

