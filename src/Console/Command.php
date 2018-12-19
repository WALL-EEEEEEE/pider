<?php

namespace Pider\Console; 

/**
 * Terminal agent of console component from Symfony
 */
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\ArrayInput;

class Command extends BaseCommand{

    protected function help(InputInterface $in, OutputInterface $out) {
        $command=$this->getApplication()->find('help');
        $command_name = $this->getName();
        $argument = array(
            'command_name'=> $command_name,
        );
        $helpInput = new ArrayInput($argument);
        $command->run($helpInput,$out);
    }

    protected function list() {
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

    public function execute(InputInterface $in, OutputInterface $out) {
        if(!$this->validArguments($in)) {
             $this->help($in,$out);
        }
    }

    protected function validArguments(InputInterface $in) {
        $isvalid = true;
        $required = [];
        $arguments =  $this->getDefinition()->getArguments();
        $options = $this->getDefinition()->getOptions();
        foreach($arguments as $argument) {
            $required[] = $argument->getName();
        }
        foreach($options as $option) {
            if ($option->isValueRequired()) {
                $required[] = $option->getName();
            }
        }
        $in_arguments = $in->getArguments();
        $in_options  = $in->getOptions();
        $all = array_merge($in_arguments,$in_options);
        $miss_values = array_filter($all,function($value){
            if (is_null($value)) {
                return true;
            }
            return false;
        });
        if (!is_null($all['command'])) {
            $miss_values['command'] = $all['command'];
        }
        $miss_names =  array_keys($miss_values);
        $isvalid = count(array_diff($required,$miss_names))>0?$isvalid:false;
        return $isvalid;
    }

    protected function getArgumentCount() {
        $def = $this->getDefinition();
        $arguments_count = count($def->getArguments());
        return $arguments_count;
    }

    protected function getArgumentRequiredCount() {
        $def = $this->getDefinition();
        return $def->getArgumentRequiredCount();

    }
}
