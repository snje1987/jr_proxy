<?php

namespace App;

use Workerman\Worker;

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

App::init();

$http_server = new Server\ProxyServer();

if (!defined('IN_ALL')) {
    Worker::runAll();
}
