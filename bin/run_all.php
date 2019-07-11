<?php

namespace App;

use Workerman\Worker;

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

define('IN_ALL', true);

require __DIR__ . '/run_web.php';
require __DIR__ . '/run_proxy.php';

Worker::runAll();
