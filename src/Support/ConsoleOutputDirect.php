<?php
namespace Pider\Support;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\FormatterHelper;

trait ConsoleOutputDirect {

    public function redirect() {
        ob_start([$this,'beautify'],1);
    }

    public function beautify(string $out,int $phase ) {
        $output = new StreamOutput(fopen('php://stdout','w'));
        #$input = new StringInput($out);
        #$style = new SymfonyStyle($input,$output);
        $formatter = new FormatterHelper();
        $formattedLine = $formatter->formatSection('Output',$out);
        if (!empty($out)) {
            $output->writeln($formattedLine);
        } 
        return '';
    }
}

