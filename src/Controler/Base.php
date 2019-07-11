<?php

namespace App\Controler;

class Base extends BaseControler {

    public function __construct($router) {
        parent::__construct($router);
    }

    public function c_index() {
        $msg = <<<EOT
全局代理端口：14201<br />
自动代理路径：14200/proxy
EOT;
        \Workerman\Protocols\Http::header('Content-type:text/html');
        $this->send($msg);
    }

    public function c_proxy() {
        $host = isset($_SERVER['HTTP_HOST']) ? strval($_SERVER['HTTP_HOST']) : '';
        $host = preg_replace('/^([^:]*):.*$/', '$1', $host);

        $msg = <<<EOT
function FindProxyForURL(url, host)
{
    proxy = "PROXY $host:14201";
    if (shExpMatch(host, "*.jr.moefantasy.com"))
        return proxy;
    if (shExpMatch(host, "version.channel.jr.moefantasy.com"))
        return proxy;
    return "DIRECT";
}
EOT;

        \Workerman\Protocols\Http::header('Content-type:text/plain');
        $this->send($msg);
        return;
    }

}
