<?php
namespace Pider\Support\Traits;
use Pider\Kernel\Process\Processd;
use Pider\Kernel\Process\Process;

trait SpiderTwigTrait{
    protected $processes = 1;

    public function twigs(array $requests) {
        $processes = $this->processes;
        if (empty($processes) || $processes == 1) {
            $this->emitStreams($requests);
       } else {
            $steam = new Processd();
            $count = (int)(count($requests)/$processes);
            for($i = 0; $i < $processes ; $i++) {

                $slice_requests = array_slice($requests,$i,$count);
                $twig = new Process(function() use ($slice_requests){
                    $this->emitStreams($slice_requests);
                });
                $steam->add($twig);
            }
            $steam->run();
        }
    } 
}


