<?php

namespace App;

use Exception;
use Workerman\Worker;

class Config {

    protected static $cfg = null;
    protected static $inited = false;

    public static function init() {
        self::$inited = true;

        $tmp_dir = APP_ROOT . self::get('main', 'tmp_dir', '/tmp');

        if (!file_exists($tmp_dir)) {
            mkdir($tmp_dir, 0777, true);
        }

        Worker::$logFile = $tmp_dir . '/workerman.log';
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
