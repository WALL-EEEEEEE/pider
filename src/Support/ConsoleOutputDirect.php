<?php
namespace Pider\Support;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Formatter\OutputFormatter;


trait ConsoleOutputDirect {

    public function redirect() {
        ob_start([$this,'beautify'],1);
    }

    public function beautify(string $out,int $phase ) {
        $output = new StreamOutput(fopen('php://stdout','w'));
        #$input = new StringInput($out);
        #$style = new SymfonyStyle($input,$output);
        $formatter = new FormatterHelper();
        //get section
        $has_loglevel = preg_match('/^\s*(?:\[(.*)\]).*/i',$out,$matches);
        $loglevel = 'Output';
        if ($has_loglevel) {
            $loglevel= $matches[1];
            //strip level info
            $out = preg_replace('/\['.$loglevel.'\]/i','',$out);
        }
        $format_level = strtoupper($loglevel);
        switch($format_level) {
        case 'OUTPUT':
            $formattedLine = $formatter->formatSection($loglevel,$out);
            break;
        case 'DEBUG':
            $out = '<comment>'.$out.'</>';
            $section = '<fg=yellow;options=bold>'.$loglevel.'</>';
            $formattedLine = $formatter->formatSection($section,$out,'comment');
            break;
        case 'ERROR':
            $out = '<fg=red>'.$out.'</>';
            $section = '<fg=red;options=bold>['.$loglevel.']</>';
            $formattedLine = $section.' '.$out;
            break;

        default:
            $formattedLine = $formatter->formatSection($loglevel,$out);
        }
        /**
        $output->writeln('<fg=green>foo</>');
        $output->writeln('<fg=black;bg=magenta>foo</>');
        $output->writeln('<bg=yellow;options=bold>foo</>');
        $output->writeln('<bg=yellow;options=blink>foo</>');
        $output->writeln('<options=bold,underscore>foo</>');
        $output->writeln('<options=bold,underscore>foo</>');
        */
        $defined_level = defined('LOG_LEVEL')?LOG_LEVEL:'OUTPUT';
        $level_match = strtoupper($defined_level) == strtoupper($loglevel);
        if (!empty($out) && $level_match) {
            $output->writeln($formattedLine);
        } 
        return '';
    }


}

