<?php

namespace App\Model\Skill;

use Exception;
use App\Model\Skill;
use App\App;

/**
 * 狼群战术
 * 队伍中每有一艘潜艇，都会增加所有潜艇的命中值1/2/2点及暴击率1%/1%/2%，这个技能只在旗舰是U型潜艇时生效
 */
class Skill_75 extends Skill {

    const BUFF_ATTR = [
        App::BATTLE_PROP_HIT => [0, 1, 2, 2],
    ];
    const U_SUB = [
        '10019711' => 'U47',
        '10019811' => 'U505',
        '10028911' => 'U81',
        '10029011' => 'U96',
        '10029211' => 'U156',
        '10029311' => 'U1206',
        '10035111' => 'U-1405',
        '10040211' => 'U-35',
        '11019711' => 'U47',
        '11028911' => 'U81',
        '10038611' => '莉安夕',
    ];

    /**
     * @param int $from_index
     * @param \App\Model\Fleet $self_fleet
     * @param \App\Model\Fleet $enemy_fleet
     */
    public function apply($from_index, $self_fleet, $enemy_fleet = null) {
        if ($this->level <= 0) {
            return;
        }

        $ships = $self_fleet->get_ships();
        $flag_ship = $self_fleet->get_ship(0);
        $ship_cid = $flag_ship->ship_cid;

        if (!isset(self::U_SUB[$ship_cid])) {
            return;
        }

        $count = 0;
        foreach ($ships as $index => $ship) {
            if ($ship->type == App::SHIP_TYPE_SS) {
                $count ++;
            }
        }

        foreach ($ships as $index => $ship) {
            if ($ship->type == App::SHIP_TYPE_SS) {
                $ship->add_skill_buff(App::BATTLE_PROP_HIT, self::BUFF_ATTR[App::BATTLE_PROP_HIT][$this->level] * $count);
            }
        }
    }

}
