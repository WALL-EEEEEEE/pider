<?php

namespace Pider\Console; 

/**
 * Terminal agent of console component from Symfony
 */
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Pider\Support\ConfigSetup;
use Pider\Support\ConsoleOutputDirect;

class Terminal extends Application {
    use ConsoleOutputDirect;

    public function __construct() {
        new ConfigSetup();
        $this->redirect();
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
