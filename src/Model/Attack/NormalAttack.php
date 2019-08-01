<?php

namespace App\Model\Attack;

use Exception;
use App\App;

class NormalAttack extends BaseAttack {

    public function __construct() {
        parent::__construct();
    }

    public function calc_range() {

        $this->calc_common_var();

        $this->from->on_attack($this, 1);
        $this->to->on_attack($this, 2);

        $atk = $this->from->get_battle_prop(App::BATTLE_PROP_ATK);

        $base_atk = $atk + 5;
        $other_vars = $base_atk * $this->formation_var * $this->war_type_var * $this->ammo_var * $this->hp_var * $this->critical_var * $this->skill_var;

        $min_atk = $other_vars * self::RANDOM_RANGE[0];
        $max_atk = $other_vars * self::RANDOM_RANGE[1];

        $def = $this->to->get_battle_prop(App::BATTLE_PROP_DEF);
        $target_hp = $this->to->res['hp'];

        $min_damage = ceil($min_atk * (1 - ($def / (0.5 * $def + 0.6 * $min_atk))) * $this->damage_var * $this->damage_range[0]) + $this->damage_add;
        $max_damage = ceil($max_atk * (1 - ($def / (0.5 * $def + 0.6 * $max_atk))) * $this->damage_var * $this->damage_range[1]) + $this->damage_add;

        if ($min_damage < 0) {
            $min_damage = 0;
        }
        if ($max_damage < 0) {
            $max_damage = ceil(min([$base_atk, $target_hp]) / 10);
        }

        return [$min_damage, $max_damage];
    }

    const RANDOM_RANGE = [0.89, 1.22];
    const FORMATION_VAR = [
        1 => 1, //'单纵',
        2 => 0.8, //'复纵',
        3 => 0.75, //'轮型',
        4 => 1, //'梯形',
        5 => 0.8, //'单橫',
    ];
    const WAR_TYPE_VAR = [
        1 => 1, //'同航战',
        2 => 0.8, //'反航战',
        3 => 1.15, //'T字有利',
        4 => 0.65, //'T字不利',
    ];

}
