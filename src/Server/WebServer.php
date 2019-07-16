<?php

namespace App\Server;

use Workerman\Worker;

class WebServer {

    public function __construct() {
        $port = \App\Config::get('web_server', 'port', 14200);

        $http_worker = new Worker("http://0.0.0.0:$port");
        $http_worker->count = 2;
        $http_worker->onMessage = [$this, 'on_message'];
        $http_worker->name = 'WebServer';
    }

    public function on_message($connection, $data) {
        $uri = isset($_SERVER['REQUEST_URI']) ? strval($_SERVER['REQUEST_URI']) : '';

        $router = new \App\Router($connection);
        $router->route($uri);

        return;
    }

}
