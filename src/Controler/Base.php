<?php

namespace App\Controler;

class Base extends BaseControler {

    public function __construct($router) {
        parent::__construct($router);
    }

    public function c_index() {
        $proxy_port = \App\Config::get('proxy_server', 'port', 14201);
        $web_port = \App\Config::get('web_server', 'port', 14200);

        $this->display_tpl('index', [
            'proxy_port' => $proxy_port,
            'web_port' => $web_port
        ]);
    }

    public function c_proxy() {
        $host = isset($_SERVER['HTTP_HOST']) ? strval($_SERVER['HTTP_HOST']) : '';
        $host = preg_replace('/^([^:]*):.*$/', '$1', $host);

        $this->display_tpl('proxy', [
            'host' => $host,
                ], 'text/plain');
    }

}
