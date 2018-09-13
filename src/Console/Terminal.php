<?php

namespace Pider\Console; 

/**
 * Terminal agent of console component from Symfony
 */
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class Terminal extends Application {

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
