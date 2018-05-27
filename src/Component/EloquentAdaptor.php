<?php

/**
 * @class EloquentAdaptor
 * EloquentAdaptor supply well database interactiion for scraping and anlyzing process with * making use of Eloquent ORM library.  
 */
namespace Pider\Component;

use Pider\Kernel\Kernel as Kernel;
use Pider\Exceptions\DatabaseConfigException;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

class EloquentAdaptor {
    private $DBConfigs = [];

    public function __construct(Kernel $kernel) {
        $this->DBConfigs = $kernel->Configs['Database'];
    }
    public function init() {
        $capsule = new Capsule;
        if (empty($this->DBConfigs)) {
            throw new DatabaseConfigException('Not any valid database connection specified in your configs, Please config it at first !');
        } else {
            foreach($this->DBConfigs as $cname => $cconf) {
                $capsule->addConnection($cconf, $cname);
            }
            $capsule->setEventDispatcher(new Dispatcher);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        }
   }
}
