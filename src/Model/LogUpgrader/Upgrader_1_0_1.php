<?php

namespace App\Model\LogUpgrader;

use Exception;

class Upgrader_1_0_1 {

    public function do_upgrade($raw_data) {

        if (!isset($raw_data['war_day']) || !isset($raw_data['war_day']['warReport'])) {
            throw new Exception();
        }

        if (!isset($raw_data['fleet'])) {
            $raw_data['fleet'] = [];
            foreach ($raw_data['war_day']['warReport']['selfShips'] as $ship) {
                $ship['hp_max'] = $ship['hpMax'];
                unset($ship['hpMax']);

                $ship['ammo'] = 0;
                $ship['ammo_max'] = 0;
                $ship['oil'] = 0;
                $ship['oil_max'] = 0;

                $raw_data['fleet'][] = $ship;
            }
        }

        $raw_data['version'] = '1.0.2';
        return $raw_data;
    }

}
