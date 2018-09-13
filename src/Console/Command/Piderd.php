<?php
namespace Pider\Console\Command;

/**
 * Define `piderd` command
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Piderd extends Command {
    public $isExec = true;

    protected function configure() {
        $this->setName('piderd');
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
    }
}

