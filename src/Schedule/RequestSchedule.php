<?php 
namespace Pider\Schedule;

use Pider\Kernel\WithKernel;
use Pider\Kernel\WithStream;
use Pider\Kernel\Stream;
use Pider\Kernel\Schedule;

class  RequestSchedule extends WithKernel implements Schedule{
    private $request_pool = [];
    private $current_request ;
    /**
     * @method add()
     * Add task to Schedule 
     */
    public function add($item) {
        $this->request_pool[] = $item;
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
        //Extract request from stream and put the request into shceduler
        $request_schedule = $kernel->RequestSchedule;
        $request_schedule->add($stream->body());
        $this->current_request = $stream;
    }

    /**
     * @method toStream()
     *
     */
    public function toStream() {
        return $this->current_request;
    }

    /** 
     * @method isStream()
     * Limit the stream to Request 
     * */
    public function isStream(Stream $stream) {
        return parent::isStream($stream)?($stream->type() == "REQUEST"?true:false):false;
    }
}

