<?php

namespace App;

use Exception;
use Workerman\Worker;

class Config {

    protected static $cfg = null;
    protected static $inited = false;

    public static function init() {
        self::$inited = true;

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

    public static function load() {

        if (!defined('APP_ROOT')) {
            define('APP_ROOT', str_replace('\\', '/', dirname(__DIR__)));
        }

        $file = APP_ROOT . '/config.php';
        if (file_exists($file)) {
            self::$cfg = require $file;
        }
        if (empty(self::$cfg)) {
            throw new Exception('config.php not found');
        }

        if (!self::$inited) {
            self::init();
        }
    }

    public static function get($section = null, $name = null, $default = null) {
        if ($section === null) {
            return self::$cfg;
        }

        if (!isset(self::$cfg[$section])) {
            return $default;
        }

        if ($name === null) {
            return self::$cfg[$section];
        }

        if (!isset(self::$cfg[$section][$name])) {
            return $default;
        }

        return self::$cfg[$section][$name];
    }

}
