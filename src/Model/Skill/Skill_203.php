<?php

namespace App\Model\Skill;

use Exception;
use App\Model\Skill;

/**
 * 高速小队
 * 增加相邻两个单位（限驱逐舰和轻巡）的航速2/3/4点和回避值4/8/12点，命中敌方时会造成额外10/15/20点伤害；当伟大的庞贝位于舰队中时，额外增加自身和伟大的庞贝两个单位的命中值5/10/15点和5/10/15暴击率
 */
class Skill_203 extends Skill {

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
        foreach ($ships as $index => $ship) {
            if ($ship->ship_cid == '10033712') {
                $ship->add_skill_buff('hit', $this->level * 5);
                $self = $self_fleet->get_ship($from_index);
                if ($self !== null) {
                    $self->add_skill_buff('hit', $this->level * 5);
                }
            }

            if ($index == $from_index - 1 || $index == $from_index + 1) {
                if ($ship->type == 10 || $ship->type == 12) {
                    $ship->add_skill_buff('speed', $this->level + 1);
                    $ship->add_skill_buff('miss', $this->level * 4);
                }
            }
        }
    }

}
