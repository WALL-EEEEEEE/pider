<?php
namespace Pider\Log;

/**
 * @class Log
 * Log tool to record log message
 */

class Log {
     const LOG_EMERG=0;    //system is unusable
     const LOG_ALERT=1;    //action must be taken immediately
     const LOG_CRIT=2;     //critical conditions
     const LOG_ERR=3;      //error conditions
     const LOG_WARNING =4; //warning conditions
     const LOG_NOTICE=5;   //normal, but significant, condition
     const LOG_INFO=6;     //informational message
     const LOG_DEBUG=7;    //debug-level message

     private $wrapper = [
         self::LOG_EMERG=> '[EMERGE]',
         self::LOG_ALERT => '[ALERT]',
         self::LOG_CRIT  => '[CRITIC]',
         self::LOG_ERR   => '[ERROR]',
         self::LOG_WARNING=> '[WARNING]',
         self::LOG_NOTICE => '[NOTICE]',
         self::LOG_INFO   => '[INFO]',
         self::LOG_DEBUG  => '[DEBUG]'
     ];
     

     public static function getLogger() {
         return new Log();
     }

     public function log(string $msg, int $level) {
         $levels = array_keys($this->wrapper);
         if (!in_array($level,$levels)) {
             throw new LogLevelError("No such log level.");
         }
         $prefix = $this->wrapper[$level];
         echo $prefix.' '.$msg;
     }

     /**
      * @method info
      * logging info level message
      * @param string $msg 
      */
     public function info(string $msg) {
         $this->log($msg,self::LOG_INFO);
     }

     /**
      * @method error
      * logging error level message
      * @param string $msg
      *
      */
     public function error(string $msg) {
         $this->log($msg,self::LOG_ERR);
     }
     /**
      * @method notice
      * logging notice level message
      * @param string $msg
      */
     public function notice(string $msg) {
         $this->log($msg,self::LOG_NOTICE);
     }

     /**
      * @method debug
      * logging debug level message
      * @param string $msg
      */
     public function debug(string $msg) {
         $msg = '<'.strftime('%Y-%m-%d %H:%M:%S').'> '.$msg;
         $this->log($msg,self::LOG_DEBUG);
     }
     /**
      * @method warning
      * logging warning level message
      * @param string $msg
      *
      */
     public function warning(string $msg) {
         $this->log($msg,self::LOG_WARNING);
     }
}

