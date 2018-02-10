<?php

return $kernel_config = [
    "Cores"=> [   
        "Pider\Kernel\Core\ScheduleLoader",
        "Pider\Kernel\Core\ModuleLoader",
        "Pider\Kernel\Core\ComponentLoader",
     ],
     "Components"=> [
     ],
    "Config"=> [
        'Config/config.php',
    ]
];
