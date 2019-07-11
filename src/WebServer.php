<?php

namespace Site;

use Workerman\Worker;

class WebServer {

    public function __construct($ip = '0.0.0.0', $port = 14200) {
        $http_worker = new Worker("http://$ip:$port");
        $http_worker->count = 4;
        $http_worker->onMessage = [$this, 'on_message'];
        $http_worker->name = 'WebServer';
    }

    public function on_message($connection, $data) {

        $host = isset($_SERVER['HTTP_HOST']) ? strval($_SERVER['HTTP_HOST']) : '';
        $host = preg_replace('/^([^:]*):.*$/', '$1', $host);

        if (empty($host)) {
            $connection->send("connection error.\n");
            return;
        }

        $msg = <<<EOT
function FindProxyForURL(url, host)
{
    proxy = "PROXY $host:14201";
    if (shExpMatch(host, "version.jr.moefantasy.com"))
        return proxy;
    if (shExpMatch(host, "version.channel.jr.moefantasy.com"))
        return proxy;
    return "DIRECT";
}
EOT;

//    if (shExpMatch(host,"version.channel.jr.moefantasy.com"))
//	    return proxy;
        \Workerman\Protocols\Http::header('Content-type:text/plain');
        $connection->send($msg);
        return;
    }

}
