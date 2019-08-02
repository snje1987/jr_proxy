<?php

namespace App\Model\Skill;

use Exception;
use App\Model\Skill;
use App\App;

/**
 * 矢志不渝
 * 提升全队的装甲、回避、对空值3/4/5点，对C国船只3倍效果
 */
class Skill_72 extends Skill {

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
        foreach ($ships as $ship) {
            $num = $this->level + 2;
            if ($ship->country == 8) {
                $num *= 3;
            }
            $ship->add_skill_buff(App::BATTLE_PROP_DEF, $num);
            $ship->add_skill_buff(App::BATTLE_PROP_MISS, $num);
            $ship->add_skill_buff(App::BATTLE_PROP_AIRDEF, $num);
        }
    }

}
