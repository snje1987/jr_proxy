<?php

namespace Site;

use Workerman\Worker;

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

$http_server = new WebServer();

if (!defined('IN_ALL')) {
    Worker::runAll();
}
