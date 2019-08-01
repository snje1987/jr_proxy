<?php

namespace App\Model\Skill;

use Exception;
use App\Model\Skill;
use App\App;

/**
 * 皇家巡游
 * 当胡德作为旗舰时，提升全队航速2/3/4点
 */
class Skill_63 extends Skill {

    /**
     * @param int $from_index
     * @param \App\Model\Fleet $self_fleet
     * @param \App\Model\Fleet $enemy_fleet
     */
    public function apply($from_index, $self_fleet, $enemy_fleet = null) {
        if ($this->level <= 0) {
            return;
        }
        if ($from_index != 0) {
            return;
        }
        $ships = $self_fleet->get_ships();
        foreach ($ships as $ship) {
            $ship->add_skill_buff(App::BATTLE_PROP_SPEED, $this->level + 1);
        }
    }

}
