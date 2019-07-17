<?php

namespace App;

use Exception;
use Workerman\Worker;

class Config {

    protected static $cfg = null;

    public static function load() {
        $file = APP_ROOT . '/config.php';
        if (file_exists($file)) {
            self::$cfg = require $file;
        }
        if (empty(self::$cfg)) {
            throw new Exception('config.php not found');
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
