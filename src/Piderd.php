<?php
require_once('Pider.php');
use Twig\Pollen\Protocols\Osci_v1 as Osci;
use Twig\Pollen\Oscillated;
use Twig\Pollen\Oscillate;
use Twig\Process\Processd   as Processd;
use Twig\Process\Process    as Process;
use Pider\Support\URLCenter as URLCenter;

/**
 * @class Piderd
 *
 * Support distributed facilities
 */

class Piderd {

    /**
     * @method runAsServer()
     * Start a Osci server as sever end
     */
    public static function runAsServer() {
        $ucenter = new URLCenter();
        $pm = new Processd('Piderd');
        $process = new Process(function() use ($ucenter){
            $osci_server = new Oscillated('127.0.0.1', 1180);
            $osci_server->on('GET_URL_FILTER',function($connection, $domain) use ($ucenter) {
                var_dump("GET URL from $domain");
                return $ucenter->getOne().PHP_EOL;
            });
            $osci_server->on('GET_URL',function() use ($ucenter){
                var_dump("GET URL");
                return $ucenter->getOne().PHP_EOL;
            });
            $osci_server->on('PUT_URL',function($connection,$url) use($ucenter){
                $ucenter->putOne($url);
            });
            $osci_server->listen();
        },'Piderd[Osci]');
        $pm->add($process);
        $pm->run();
    }

    /**
     *@method runAsClient() 
     *
     * Start a Osci client as client end
     */
    public static function runAsClient() {
    }
}
