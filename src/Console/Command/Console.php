<?php
namespace Pider\Console\Command;

/**
 * Define `pider` command under console
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class Console extends Command {

    public $isExec = True;

    public function subcommands() {
        $subcommands = [];
        $path = PIDER_PATH."/Console/Command/Console/";
        $classes = array_diff(scandir($path),['..','.']);
        foreach($classes as  $cls) {
            if(is_file($path.$cls)) {
                include_once($path.$cls);
            } else {
                continue;
            }
            #var_dump($path.$cls);
            $cls = "Pider\Console\Command\Console\\".basename($cls,'.php');
            if(class_exists($cls,True)) {
                $ncls = new $cls();
                if($ncls instanceof Command) {
                    $subcommands[] = new $ncls();
                }
            }
        }
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

    protected function configure() {
        $this->setName('console')
            ->setDescription("Manage your scripts")
            ->addUsage('./console [commands]')
            ->setHelp("This tools is used to manage your scripts")
            ->setHidden(true);
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input,$output);
        $command_list = $this->list_commands();
        $io->writeln(array("<comment>Name:</comment>"));
        $io->text($this->getName());
        $io->newLine();
        $io->writeln(array("<comment>Description:</comment>"));
        $io->text("Manage your scripts");
        $io->newLine();
        $io->writeln(array("<comment>Available commands</comment>"));
        $io->text($command_list);
 
    }
}

