<?php

namespace App\Model\Skill;

use Exception;
use App\Model\Skill;
use App\App;

/**
 * 先锋
 * 炮击命中敌方航速大于等于27的单位时造成额外5/10/15点固定伤害。自身相邻上下单位开闭幕导弹、航空战时所受到的伤害降低10%/15%/20
 */
class Skill_207 extends Skill {

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

        $indexs = [$from_index - 1, $from_index + 1];

        foreach ($indexs as $index) {
            $ship = $self_fleet->get_ship($index);
            if ($ship !== null) {
                $ship->add_attack_hook([$this, 'on_damage']);
            }
        }
    }

    /**
     * @param \App\Model\DamageCalc\BaseAttack $attack
     */
    public function on_attack($attack, $side) {
        if ($side != 1) {
            return;
        }

        if ($attack->group_name != 'normal_attack' && $attack->group_name != 'normal_attack2') {
            return;
        }

        $speed = $attack->to->get_battle_prop('speed');
        if ($speed >= 27) {
            $attack->add_damage_add(5 * $this->level);
        }
    }

    /**
     * @param \App\Model\DamageCalc\BaseAttack $attack
     */
    public function on_damage($attack, $side) {
        if ($side != 2) {
            return;
        }
    }

}
