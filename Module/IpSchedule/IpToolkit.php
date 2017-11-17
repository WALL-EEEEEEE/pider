<?php
namespace Module\IpSchedule;

/**
 * @class IpToolkit
 *
 * Encapsulate some useful toolkit to detect availibility,qunlity for ip
 */

class IpToolkit {
    /**
     * @method function pingTimes($ip,$times=10)
     * @param  $ip Ip to be tested
     * @param  $times number of  ping requests,default to be ten.
     * @return int time costed for a ping request,the time is a mean value of total time costed and the number of requests (ms)
     */
    public static function pingTimes(string $ip,int $times = 10) :int {
        if (empty($ip)) {
            throw new \InvalidArgumentException("IpToolkit->pingTimes: Argument #1 must be a non-empty string!");
        }
        $elapse_total = 0;  
        for($i = 0; $i < $times; $i++) {
            $per_elapse = self::ping($ip);
            if ($per_elapse != -1 ) {
                $elapse_total += $per_elapse;
            }
        }
        return round($elapse_total/$times,2);
    }
    /**
     * @method function ping(sring $ip, int $timeout): int 
     * @param $ip ip to ping
     * @param $timeout longest time to wait  for the ICMP response
     * @return int time ping operation costs, ms
     */
    public static function ping(string $ip, int $timeout = 2):int {
        if (empty($ip)) {
            throw new \InvalidArgumentException("IpToolkit->pingTimes: Argument #1 must be a non-empty string!");
        }
        $ICMP_Package= "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
        $ICMP_Socket = socket_create(AF_INET,SOCK_RAW,1);
        socket_set_option($ICMP_Socket,SOL_SOCKET, SO_RCVTIMEO,['sec'=>$timeout,'usec'=>0]);
        socket_connect($ICMP_Socket, $ip,null);
        $start_micro = microtime(true);
        socket_send($ICMP_Socket,$ICMP_Package, strlen($ICMP_Package),0);
        $ICMP_Status = socket_read($ICMP_Socket,255);
        $end_micro   = microtime(true);
        socket_close($ICMP_Socket);
        $elapse_micro = $end_micro - $start_micro;
        if (!$ICMP_Status) {
            return -1;
        }
        return round($elapse_micro*1000,2);

    }
}
