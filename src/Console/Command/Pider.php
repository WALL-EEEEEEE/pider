<?php
namespace Pider\Console\Command;

/**
 * Define pider command under console
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Pider extends Command {

    public $isExe= true;

    protected function configure() {

    }
    protected function execute(InputInterface $input, OutputInterface $output) {

    }
}

