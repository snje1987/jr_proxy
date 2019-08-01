<?php

namespace App\Model\Skill;

use Exception;
use App\Model\Skill;
use App\App;

/**
 * 直卫空母
 * 降低敌方全体战列、战巡的对空值5/10/15点、命中值3/6/9点
 */
class Skill_192 extends Skill {

    /**
     * @param int $from_index
     * @param \App\Model\Fleet $self_fleet
     * @param \App\Model\Fleet $enemy_fleet
     */
    public function apply($from_index, $self_fleet, $enemy_fleet = null) {
        if ($this->level <= 0 || $enemy_fleet === null) {
            return;
        }

        $ships = $enemy_fleet->get_ships();
        foreach ($ships as $ship) {
            if ($ship->type == App::SHIP_TYPE_BB || $ship->type == App::SHIP_TYPE_BC) {
                $ship->add_skill_buff(App::BATTLE_PROP_AIRDEF, $this->level * -5);
                $ship->add_skill_buff(App::BATTLE_PROP_HIT, $this->level * -3);
            }
        }
    }

}
