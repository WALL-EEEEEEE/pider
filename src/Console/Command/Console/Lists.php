<?php
namespace Pider\Console\Command\Console;

/**
 * Define `pider` command under console
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Lists extends Command {

    private function list_scripts(){
        $default_scripts_path = PIDER_PATH."/Console/Command";
        $scripts = array_diff(scandir($default_scripts_path),['.','..']);
        $scripts = array_filter($scripts,function($value) use($default_scripts_path) {
            $path = $default_scripts_path.'/'.$value;
            if(is_file($path)) {
                return true;
            } 
            return false;
        });

        return $this->retrive_names($scripts,$default_scripts_path);
    }
    private function retrive_names($scripts,$path) {
        $command_names = [];
        foreach($scripts as $script) {
            $cls_file = $path.'/'.$script;
            if(file_exists($cls_file)) {
                @include_once($cls_file);
                $cls = basename($script,'.php');
                $namespace = "Pider\Console\Command\\";
                $cls = $namespace.$cls;
                if(class_exists($cls)) {
                    $ncls = new $cls();
                    if($ncls instanceof Command) {
                        $command_name = $ncls->getName();
                        if (!empty($command_name)) {
                            $command_names[] = $ncls->getName();
                        }

                    }
                }
            }
        }
        return $command_names;
    }

    protected function configure() {
        $this->setName('list')
            ->setDescription("List current avaible scripts")
            ->setHelp("This command allows list all aviable scripts defined");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input,$output);
        $io->writeln(["<comment>Available scripts:</comment>"]);
        $io->text($this->list_scripts());
    }
}

