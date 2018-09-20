<?php
namespace Pider\Support;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Formatter\OutputFormatter;


trait ConsoleOutputDirect {
   //priority of loglevel, low to high as defined order as array items.
   //OUTPUT < DEBUG < NOTICE < WARN < ERROR
   //It also controls the output behavior of different level log message.
   //All rules followed is that the highers must be outputed more prioritily
   //than the lowers.For example, if `OUTPUT` loglevel is admited, all loglevels 
   //prior than  `OUTPUT` mustn't be ignored. 
   private $level_priority = [
	   'OUTPUT',
	   'DEBUG',
	   'NOTICE',
	   'WARN',
	   'ERROR',
   ];

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
	$defined_level = defined('LOG_LEVEL')?LOG_LEVEL:'OUTPUT';
	$defined_level = strtoupper($defined_level);
	$level_allowed = $this->level_priority;
	if (in_array($defined_level,$this->level_priority)){
		$pos = array_search($defined_level,$this->level_priority);
		$level_allowed = array_slice($this->level_priority,$pos);
	} 
        if (!empty($out) && in_array($format_level,$level_allowed)) {
            $output->writeln($formattedLine);
        } 
        return '';
    }


}

