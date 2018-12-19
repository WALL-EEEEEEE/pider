<?php
namespace Pider\Schedule;

/**
 * @class IpSchedule 
 *
 * Schedule proxy ips for request 
 */
use Pider\Kernel\WithKernel;
use Pider\Kernel\WithStream;
use Pider\Kernel\Stream;
use Pider\Kernel\MetaStream;
use Pider\Kernel\ConfigError;
use Pider\Kernel\Schedule;
use Pider\Kernel\ScheduleError;
use Pider\Extension\autoloader;
use Pider\Log\Log as Logger;

class IpSchedule extends WithKernel implements Schedule {
    /**
     * @property $ippool 
     * store proxy ips 
     */
    private $ippool =  [];
    /**
     * @property $urlpool 
     * store requests  urls, applied in a furthur optimazation for different urls 
     */
    private $urlpool = [];

    private $run_mode = 0;

    private $ippool_limit = 20;
    private $sources = [];
    private $proxy_enable = false;
    private $fromstream;
    private $logger;

    /**
     * @property MODE_STANDALONE 
     * IpSchedule will not exit along with other application used it, it will still serve for the request  on backend if run in this mode
     */
    private const MODE_STANDALONE =  1;
    /**
     * @property MODE_BOUND
     * IpSchedule in this mode will exit with other application used it.This is the default mode.
     */
    private const MODE_BOUND      =  0;

    /**
     *@method __construct($limit_count, $mode = 0)
     *
     *@param $limit_count int  the capacibilty of ips, IpSchedule can store, 20 by default.
     *@param $mode   const int the mode IpSchedule will run under. There are two modes, MODE_SANDALONE, MODE_BOUND
     */
    public function __construct(int $limit_count = 5, int $mode = 0 ) {
        $this->logger = Logger::getLogger();
        $this->ippool_limit = $limit_count;
        if ( $mode != self::MODE_BOUND && $mode != self::MODE_STANDALONE) {
            throw new \InvalidArgumentException("Unknown mode specified!");
        } else {
            $this->run_mode = $mode;
        }
    }
    /**
     * @method source($callback)
     * Set up the ip source for IpSchedule,IpSchedule will call it in order to get the ip once the number of ip is less than the limited.
     */
    public function source($callback) {
        if (!in_array($callback,$this->sources)) {
            $this->sources[] = $callback;
        }
    }
    /**
     * @method add(string | array $ip)
     * @param ips string|array The ips supposed to be added into IpSchedule
     * Add ip to be scheduled
     **/
    public function add($ips){
        if (!is_array($ips) &&  !is_string($ips)) {
            throw new \InvalidArgumentException("IpSchedule->add() only accept ips in string or array.");
        }
        if (is_string($ips)) {
            $ips = [$ips];
        }
        $ips = array_values($ips);
        $candidate_ips = array_diff($ips,$this->ippool); 
        $candidate_ips = $this->ipFilter($candidate_ips);
        if (count($candidate_ips) > 0 && !(count($candidate_ips) > $this->ippool_limit)) {
            //strip the invalid ip
            $this->ippool = array_merge($this->ippool,$candidate_ips);
        }
    }

    /**
     * @method schedule() 
     * Schedule ips and generate a ip priority lists
     */
    public function schedule(){
        if (empty($this->sources)) {
            throw new ScheduleError("Not any sources of ip specified, you must specify at least one source of ip!");
        }
        //fetch url
        return $this->pullIp();
    }
    /**
     * @method run()
     * Start the schedule process
     */
    public function run() {

        if ($this->run_mode == self::MODE_STANDALONE ) {
            $ScheduleDaemon = new ScheduleDaemon($this);
            $ScheduleDaemon->run();
        } else {
            $this->schedule();
       }
    }

    /**
     * @method deliver($count,$url) 
     * @arg $count int    the number of ips should be provided to the caller
     * @arg $url   string the url which caller want , if the url argument is provide, IpShedule will optimized agaist the url before delivered the ip  to the caller.
     * Provide the ips to the caller, 
     */
    public function deliver($count=1,$url='') {
        if (empty($url)) {
            if (!in_array($url,$this->urlpool)) { 
                $this->urlpool[] = $url;
            }
        }
        $this->schedule();
        $ip = array_pop($this->ippool);
        return $ip;
    }


    public function pullIp() {
        while(count($this->ippool) < $this->ippool_limit) {
           foreach($this->sources as $callback) {
               $src_ips = $callback();
               $this->add($src_ips);
           }
        }
    }

    /**
     *@method  ipValidate($ip) 
     *@param   $ip  Ip supposed to be validated
     * validate legitimation of a ip
     */
    private function ipValidate(string $ip) {
        $leg_regex = "/\d{1,3}\.\d{1,3}\.\d{1,3}:\d+/";
        if (empty($ip)) {
            return false;
        }
        if (preg_match($leg_regex, $ip)) {
            return true;
        }
        return false;
    }
    /**
     *@method ipFilter(ips)
     * Strip the invalide ips
     *@param  $ips string|array the ips ought to be filterd 
     *
     */
    private function ipFilter($ips) {
        $filterred_ips = [];
        if (!is_array($ips) &&  !is_string($ips)) {
            throw new InvalideArgumentException("IpSchedule->add() only accept ips in string or array.");
        }
        if (is_string($ips)) {
            $ips = [$ips];
        }
        if (count($ips) > 0) {
            foreach($ips as $ip) {
                if ($this->ipValidate($ip)) {
                    $filterred_ips[] = $ip;
                }
            }
       } else {
            return [];
        }
        return $filterred_ips;
    }

    public function isStream(Stream $stream) {
        $return = parent::isStream($stream)?($stream->type() == "REQUEST"?true:false):false;
        return $return;
    }

    public function fromStream(Stream $stream, WithStream $kernel) {
        $this->fromstream = $stream;
        //Check if IpSchedule initialized
        $if_exist = $kernel->IpSchedule;
        if (empty($if_exist)) {
            $this->proxy_enable = $kernel->Configs['Request.Option']['proxy'];
            if (!is_bool($this->proxy_enable))
            {
                throw new ConfigError("Invalid config option: proxy in Request.Option must be a  true or false boolean!");

            }
            //if proxy disabled  in config, don't process the stream
            if ($this->proxy_enable) {
                $this->logger->debug('Proxy enabled.');
                $this->logger->debug('Proxy pool capacity: '.$this->ippool_limit.'');
                $ipsources_path  = $kernel->Configs->Proxy;
                if (!is_string($ipsources_path)) {
                    throw new ConfigError("Invalid config option: Proxy must be a path string!");
                }
                $ipsources_real_path  =  APP_ROOT.DIRECTORY_SEPARATOR.$ipsources_path;
                if (!file_exists($ipsources_real_path)) {
                    throw new ConfigError("Invalid config option: Proxy must be a valid path!");
                }
                autoloader::register($ipsources_real_path);
                $sources = scandir($ipsources_real_path);
                foreach($sources as $source) {
                    if(!is_dir($source) && pathinfo($source,PATHINFO_EXTENSION)) {
                        $cls = pathinfo($source,PATHINFO_FILENAME); 
                        include_once($cls.'.php');
                        $source =  new $cls();
                        $this->source($source);
                    }
                }
            }

            $this->logger->debug("Initail proxy pool ...");
            $this->pullIp();
            $kernel->IpSchedule = $this;
            $this->logger->debug("Initail proxy pool ... done ");

        } 
    } 

    public function toStream() {
        $logger = Logger::getLogger();
        $stream = $this->fromstream;
        if ($this->proxy_enable) {
            $proxy_ip = $this->deliver();
            $request = $stream->body();
            $request->proxy($proxy_ip);
            return new MetaStream('REQUEST', $request);
        } else {
            return $stream;
        }
    }
}
