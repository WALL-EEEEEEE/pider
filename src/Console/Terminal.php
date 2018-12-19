<?php

namespace Pider\Console; 

/**
 * Terminal agent of console component from Symfony
 */
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Pider\Support\ConfigSetup;
use Pider\Support\ConsoleOutputDirect;
use Pider\Exceptions\ConfigNotFoundException;
use Pider\Exceptions\ConfigError;


class Terminal extends Application {
    use ConsoleOutputDirect;

    public function __construct() {
	$this->redirect();
        try
	{
		new ConfigSetup();
	} catch (ConfigNotFoundException $cex) {
		echo "[WARN] ".$cex->getMessage().PHP_EOL;
	} catch (ConfigError $cer) {
		echo "[ERROR] ".$cer->getMessage().PHP_EOL;
		exit(0);
	}
        parent::__construct();
    }

    public function add(Command $command) {
        $has_subcommands = method_exists($command, 'subcommands');
        if($has_subcommands) {
            $subcommands = $command->subcommands();
            if (is_string($subcommands)) {
                $subcommands = [$subcommands];
            }
            foreach($subcommands as $sub_command)  {
                parent::add($sub_command);
            }
        }
        parent::add($command);
    }
}
