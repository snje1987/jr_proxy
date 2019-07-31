<?php

namespace App\Model;

use Exception;

class LogUpgrader {

    const MIN_VERSION = '1.0.0';
    const VERSION = '1.0.1';

    public static function upgrade($file, $raw_data) {

        if (isset($raw_data['version']) && $raw_data['version'] == self::VERSION) {
            return $raw_data;
        }

        if (!isset($raw_data['version'])) {
            $raw_data['version'] = self::MIN_VERSION;
        }

        while ($raw_data['version'] !== self::VERSION) {
            $upgrader = self::create_upgrader($raw_data['version']);
            if ($upgrader === null) {
                throw new Exception('升级失败');
            }
            $raw_data = $upgrader->do_upgrade($raw_data);
        }

        $json = json_encode($raw_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        copy($file, $file . '.bak');
        file_put_contents($file, $json);

        return $raw_data;
    }

    public static function create_upgrader($version) {
        $version = str_replace('.', '_', $version);
        $class = __NAMESPACE__ . '\\LogUpgrader\\Upgrader_' . $version;
        if (class_exists($class)) {
            return new $class();
        }
        return null;
    }

}
