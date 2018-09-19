<?php
namespace Pider\Support;

use Pider\Config;

/**
 * @class ConfigSetup
 * Initiate and prepare default+project configurations
 */
class ConfigSetup {
    private $default_config = PIDER_PATH.'/Config/config.php';
    private $project_config = APP_ROOT.'/Config/config.php';

    public function __construct() {
        //load default config (alias framework config)
        $default_config = Config::fromFile($this->default_config)->toArray();
        //load project config
        $project_config = Config::fromFile($this->project_config)->toArray();
        //forge
        $configs = array_merge($default_config,$project_config);
        Config::fromArray($configs)->setAsGlobal();
    } 

}



