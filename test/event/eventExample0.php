<?php
//Read callback
function readcb($bev,$base) {
    //$input = $bev->input;
    //$bev->getInput();
    //$pos = $input->search("TTP");
    $pos = $bev->input->search("TTP");
    var_dump($bev->input->copyout($buf,1204));
    while(($n = $bev->input->drain(1024)) > 0) {
        echo $bev->input->read(1024);
    }
}

//Event callback
function eventcb($bev,$events,$base) {
    if ($events & EventbufferEvent::CONNECTED) {
        echo "Connected.\n";
    } elseif ($events & (EventBufferEvent::ERROR | EventBufferEvent::EOF)) {
        if ($events & EventBufferEvent::ERROR) {
            echo "DNS error:",$bev->getDnsErrorString(),PHP_EOL;
        }
    }
    echo "Closing\n";
//    var_dump($base);
//    $base->exit();
}
if ($argc != 3) {
   echo <<<EOS
Trivial HTTP 0.x client 
Syntax: php {$argv[0]} [hostname] [resource]
Example: php {$argv[0]} www.google.com /
EOS;
   exit();
}
$base = new EventBase();
$dns_base = new EventDnsBase($base,TRUE); // we'll use async DNS resolving
if (!$dns_base) {
    exit("Failed to init DNS Base\n");
}
$bev = new EventBufferEvent($base,NULL,EventBufferEvent::OPT_CLOSE_ON_FREE | EventBufferEvent::OPT_DEFER_CALLBACKS,'readcb', NULL,'eventcb');
if (!$bev) {
    exit("Failed creating bufferevent socket\n");
}

//$bev->setCallbacks("readcb",NULL,"eventcb",$base)
$bev->enable(Event::READ | Event::WRITE);
$output = $bev->output; //$bev->getOutput();
if (!$output->add(
    "GET {$argv[2]} HTTP/1.0\r\n".
    "Host: {$argv[1]}\r\n".
    "Connection: Close\r\n\r\n"
)) {
    exit("Failed adding request to output buffer\n");
};
if (!$bev->connectHost($dns_base,$argv[1],80,EventUtil::AF_UNSPEC)) 
{
    exit("Can't connect to host {$argv[1]}\n");
}
$base->dispatch();

