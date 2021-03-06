#!/usr/bin/env php
<?php

namespace App;

set_time_limit(0);

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

App::init();

Command\BaseCommand::run_command($argv);
