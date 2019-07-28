<?php

namespace App;

use Workerman\Worker;

class App {

    const APP_VERSION = '1.0.1';

    public static function init() {

        if (defined('APP_ROOT')) {
            return;
        }

        define('APP_ROOT', str_replace('\\', '/', dirname(__DIR__)));

        Config::load();

        define('APP_TPL_DIR', APP_ROOT . '/theme/tpl');
        define('APP_RES_DIR', APP_ROOT . '/theme/res');

        $tmp_dir = APP_ROOT . '/tmp';

        if (!file_exists($tmp_dir)) {
            mkdir($tmp_dir, 0777, true);
        }

        define('APP_TMP_DIR', $tmp_dir);

        $data_dir = APP_ROOT . '/data';

        if (!file_exists($data_dir)) {
            mkdir($data_dir, 0777, true);
        }

        define('APP_DATA_DIR', $data_dir);

        Worker::$logFile = APP_TMP_DIR . '/workerman.log';
    }

}
