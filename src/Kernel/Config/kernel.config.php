<?php

return $kernel_config = [
    "Cores"=> [   
        "Pider\Kernel\Core\ModuleLoader",
        "Pider\Kernel\Core\ScheduleLoader"
     ],
    "Extras"=> [],
    "Config"=> [
        'Config/config.php',
    ]
];
