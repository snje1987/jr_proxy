<?php

namespace App\Model\LogUpgrader;

use Exception;

class Upgrader_1_0_0 {

    public function do_upgrade($raw_data) {
        $raw_data['version'] = '1.0.1';
        return $raw_data;
    }

}
