<?php

namespace App\Model\Skill;

use Exception;
use App\Model\Skill;
use App\App;

/**
 * 对空防御
 * 自身和相邻位置的单位对空值提高10/20/30点
 */
class Skill_44 extends Skill {

    /**
     * @param int $from_index
     * @param \App\Model\Fleet $self_fleet
     * @param \App\Model\Fleet $enemy_fleet
     */
    public function apply($from_index, $self_fleet, $enemy_fleet = null) {
        if ($this->level <= 0) {
            return;
        }

        for ($i = $from_index - 1; $i <= $from_index + 1; $i++) {
            $ship = $self_fleet->get_ship($i);
            if ($ship !== null) {
                $ship->add_skill_buff(App::BATTLE_PROP_AIRDEF, $this->level * 10);
            }
        }
    }

}
