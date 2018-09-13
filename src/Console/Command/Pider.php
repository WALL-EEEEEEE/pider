<?php
namespace Pider\Console\Command;

/**
 * Define pider command under console
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Pider\Support\SpiderWise;

class Pider extends Command {

    public $isExe= true;

    protected function configure() {
        $this->setName('pider')
             ->setDescription("Project tools for <info>pider</info>")
             ->setHidden(true);
    }

    public function subcommands() {
        $subcommands = [];
        
        //define `list` subcommand
        $subcommands[] = (new class() extends Command {
            public function configure() {
                $this->setName('list')
                     ->setDescription("list all availabe spiders");
            }
        })->setCode(function($in,$out){
            $spider_path = APP_ROOT.'/examples/company';
            $spiderwise = new SpiderWise();
            $out->writeln(['<info>All Available Spiders:</info>']);
            $out->writeln($spiderwise->listSpider($spider_path));

        });
        // define `crawl` subcommand
        $subcommands[] = (new class() extends Command {
            public function configure() {
                $this->setName('crawl')
                     ->setDescription("crawl urls supplied")
                     ->addArgument('url',InputOption::VALUE_OPTIONAL,'url to crawled')
                     ->addOption('file','f',InputOption::VALUE_OPTIONAL,'file contains urls to be crawled');
            }
        })->setCode(function($in,$out){
            $command=$this->getApplication()->find('help');
            $argument = array(
                'command_name'=> 'crawl',
            );
            $helpInput = new ArrayInput($argument);
            $command->run($helpInput,$out);
       });
       // define `checkurl` subcommand
        $subcommands[] = (new class() extends Command {
            public function configure() {
                $this->setName('checkurl')
                     ->setDescription("check url can be crawled by which spiders");
            }
        })->setCode(function($in,$out){
            $command=$this->getApplication()->find('help');
            $argument = array(
                'command_name'=> 'checkurl',
            );
            $helpInput = new ArrayInput($argument);
            $command->run($helpInput,$out);
        });
 

        return $subcommands;
    }
    private function list_commands() {
        $commands = $this->getApplication()->all();
        $command_names = array_map(function($command){
            if($command->isHidden()) {
                return '';
            }
            return $command->getName();
        },$commands);
        $command_names = array_filter($command_names,function($value) {
            if (empty($value)) {
                return false;
            }
            return true;
        });
        return $command_names;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $io = new SymfonyStyle($input,$output);
        $command_list = $this->list_commands();
        $io->writeln(array("<comment>Usage:</comment>"));
        $io->text('<info>./'.$this->getName().' [command]</info>');
        $io->newLine();
        $io->writeln(array("<comment>Description:</comment>"));
        $io->text($this->getDescription());
        $io->newLine();
        $io->writeln(array("<comment>Available commands</comment>"));
        $io->text($command_list);
    }
}

