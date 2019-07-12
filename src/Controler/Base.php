<?php

namespace App\Controler;

class Base extends BaseControler {

    public function __construct($router) {
        parent::__construct($router);
    }

    public function c_index() {
        $proxy_port = \App\Config::get('proxy_server', 'port', 14201);
        $web_port = \App\Config::get('web_server', 'port', 14200);

        $msg = <<<EOT
全局代理端口：http://ip:$proxy_port<br />
自动代理路径：http://ip:$web_port/proxy<br />
<a href="/boat/index">狗粮列表</a>
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
