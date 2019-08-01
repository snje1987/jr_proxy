<?php

namespace App\Model\Skill;

use Exception;
use App\Model\Skill;
use App\App;

/**
 * 超航程战
 * 航空战阶段，提升自身前方三个位置的航母、装母、轻母10%/15%/20%的伤害。当队伍中除了自己，不含有其他航母、轻母、装母时，增加自身装甲值10/20/35点与索敌值10/18/25点，炮击战阶段，自身被攻击概率增加20%/27%/35%。
 */
class Skill_211 extends Skill {

    const BUFF_ATTR = [
        App::BATTLE_PROP_DEF => [0, 10, 20, 35],
        App::BATTLE_PROP_RADAR => [0, 10, 18, 25],
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
        $has_other = false;

        foreach ($ships as $index => $ship) {
            if ($index == $from_index) {
                continue;
            }
            if ($ship->type == App::SHIP_TYPE_CV || $ship->type == App::SHIP_TYPE_CVL || $ship->type == App::SHIP_TYPE_AV) {
                $has_other = true;
                break;
            }
        }

        if (!$has_other) {
            $ship = $self_fleet->get_ship($from_index);
            if ($ship !== null) {
                $ship->add_skill_buff(App::BATTLE_PROP_DEF, self::BUFF_ATTR[App::BATTLE_PROP_DEF][$this->level]);
                $ship->add_skill_buff(App::BATTLE_PROP_RADAR, self::BUFF_ATTR[App::BATTLE_PROP_RADAR][$this->level]);
            }
        }
    }

}
