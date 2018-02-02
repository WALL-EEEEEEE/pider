<?php

return $kernel_config = [
    "Cores"=> [   
        "Pider\Kernel\Core\ModuleLoader",
        "Pider\Kernel\Core\ScheduleLoader",
     ],
     "Extras"=> [
         "Pider\Download\Downloader"
     ],
    "Config"=> [
        'Config/config.php',
    ]
];
