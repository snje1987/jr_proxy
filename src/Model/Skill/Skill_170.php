<?php

namespace App\Model\Skill;

use Exception;
use App\Model\Skill;
use App\App;

/**
 * 神圣的战争
 * 该船的炮击伤害会在80%/85%/90%~120%/125%/130%之间浮动
 */
class Skill_170 extends Skill {

    /**
     * @param int $from_index
     * @param \App\Model\Fleet $self_fleet
     * @param \App\Model\Fleet $enemy_fleet
     */
    public function apply($from_index, $self_fleet, $enemy_fleet = null) {
        if ($this->level <= 0) {
            return;
        }

        $self = $self_fleet->get_ship($from_index);
        $self->add_attack_hook([$this, 'on_attack']);
    }

    /**
     * @param \App\Model\Attack\BaseAttack $attack
     */
    public function on_attack($attack, $side) {
        if ($side != 1) {
            return;
        }
        if ($attack->group_name == 'normal_attack' || $attack->group_name == 'normal_attack2') {
            $attack->damage_range = [(75 + $this->level * 5) / 100, (115 + $this->level * 5) / 100];
        }
    }

}
