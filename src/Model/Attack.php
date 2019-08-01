<?php

namespace App\Model;

use Exception;

class Attack {

    protected $round_group;
    protected $log;
    protected $from;
    protected $drop;
    protected $amount;
    protected $plane_type;
    protected $type;
    protected $defencer;
    protected $skill;
    protected $damage_list = [];

    /**
     * @param Attack $right
     */
    public function is_from_same($right) {
        if ($this->from[0] != $right->from[0] || $this->from[1] != $right->from[1]) {
            return false;
        }
        if (!empty($this->skill)) {
            return false;
        }
        return true;
    }

    public function __construct($round_group) {
        $this->round_group = $round_group;
        $this->log = $this->round_group->log;
    }

    public function init($info) {
        $from = $info['fromIndex'];

        if ($info['attackSide'] == 1) {
            $self_ship = 1;
            $enemy_ship = 2;
        }
        else {
            $self_ship = 2;
            $enemy_ship = 1;
        }

        $this->from = [$self_ship, $from];
        $this->type = $info['attackType'];
        $this->drop = $info['dropAmount'];
        $this->amount = $info['planeAmount'];
        $this->plane_type = $info['planeType'];

        if (!empty($info['tmdDef'])) {
            $defencer_index = $info['tmdDef'][0];
            if ($defencer_index > 0) {
                $this->defencer = [$enemy_ship, $defencer_index];
            }
        }

        if ($info['skillId'] != 0) {
            $skill = self::$game_info->get_skill_card($info['skillId']);
            if ($skill !== null) {
                $this->skill = $skill;
            }
        }

        foreach ($info['targetIndex'] as $k => $target) {
            $damage = new Damage($this->round_group, $this);
            $damage->init($info['damages'][$k], $self_ship, $enemy_ship);
            $this->damage_list[] = $damage;
        }
    }

    public function display() {
        $group_name = $this->round_group->group_name;

        $str = '';
        if ($group_name == 'open_air_attack') {
            $str .= $this->show_drop();
            if ($this->plane_type == 5) {
                return $str . '<br />';
            }
        }

        foreach ($this->damage_list as $damage) {
            $str .= ' ' . $damage->display($this->defencer) . '<br />';
        }

        return $str;
    }

    public function build_calculator() {

        if (!self::$show_damage_range) {
            return null;
        }

        $group_name = $this->round_group->group_name;

        switch ($group_name) {
            case 'normal_attack':
            case 'normal_attack2':
                if ($this->type == 1) {
                    $damage_calc = new DamageCalc\NormalAttack($group_name);
                }
                else {
                    return null;
                }
                break;
            default :
                return null;
        }


        $from = $this->log->get_ship($this->from);
        $hp_info = $this->round_group->get_ship($this->from);

        $from = clone $from;
        $from->set_hp($hp_info['hp_left']);

        $damage_calc->from = $from;

        $this->log->fill_fleet_info($damage_calc, $this->from[0]);

        return $damage_calc;
    }

    ////////////////////////////////////

    protected function show_drop() {
        if ($this->plane_type == 5) {
            $str = '<span class="btn btn-primary">战斗机</span>';
        }
        elseif ($this->plane_type == 6) {
            $str = '<span class="btn btn-primary">轰炸机</span>';
        }
        else {
            $str = '<span class="btn btn-primary">鱼雷机</span>';
        }

        $str .= '<span class="btn btn-info" style="color:black;min-width:50px;">' . $this->drop . '/' . $this->amount . '</span>';

        return '<div class="btn-group btn-group-xs">' . $str . '</div>';
    }

    public function __get($name) {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }

    protected static $game_info;
    protected static $show_damage_range = 0;

    public static function init_class() {
        self::$game_info = GameInfo::get();
        self::$show_damage_range = \App\Config::get('main', 'damage_range', 0);
    }

}

Attack::init_class();
