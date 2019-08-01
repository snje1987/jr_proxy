<?php

namespace App\Model;

use Exception;
use App\Model\CurrentWar;

class Fleet {

    protected $id;
    protected $title;

    /**
     * @var Ship[] 
     */
    protected $ships;
    //////////////////////////

    protected $extra_attr;

    public function __get($name) {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        if (isset($this->extra_attr[$name])) {
            return $this->extra_attr[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        if (!isset($this->{$name})) {
            $this->extra_attr[$name] = $value;
        }
    }

    public function __construct($id, $title) {
        $this->game_info = GameInfo::get();
        $this->id = $id;
        $this->title = $title;
    }

    public function set_ships($ships) {
        $this->ships = $ships;
    }

    /**
     * @return Ship[]
     */
    public function get_ships() {
        return $this->ships;
    }

    public function get_ship($index) {
        if (isset($this->ships[$index])) {
            return $this->ships[$index];
        }
        return null;
    }

    public function get_fleet_card() {
        if (empty($this->ships)) {
            return [];
        }
        $result = [];
        $result['speed'] = $this->calc_fleet_speed();

        return $result;
    }

    public function apply_skill($enemy_fleet = null) {
        foreach ($this->ships as $index => $ship) {
            $skill_info = $ship->skill;
            if (!empty($skill_info)) {
                $skill = Skill::get_skill($skill_info);
                if ($skill !== null) {
                    $skill->apply($index, $this, $enemy_fleet);
                }
            }
        }
    }

    public function apply_tactic($enemy_fleet = null) {
        foreach ($this->ships as $index => $ship) {
            $tactics_in_use = $ship->get_tactics_in_use();
            foreach ($tactics_in_use as $tid => $tactic_info) {
                $tactic = Tactic::get_tactic($tactic_info);
                if ($tactic !== null) {
                    $tactic->apply($index, $this, $enemy_fleet);
                }
            }
        }
    }

    /////////////////////
    protected $game_info;

    protected function calc_fleet_speed() {
        $result = [];

        $result['count'] = count($this->ships);

        $result['speed_flag_ship'] = $this->ships[0]->get_battle_prop('speed');
        $result['speed_max'] = $result['speed_flag_ship'];
        $result['speed_min'] = $result['speed_flag_ship'];
        $result['speed_sum'] = 0;

        $group_speed = [];

        foreach ($this->ships as $ship) {
            $speed = $ship->get_battle_prop('speed');

            if ($speed > $result['speed_max']) {
                $result['speed_max'] = $speed;
            }
            if ($speed < $result['speed_min']) {
                $result['speed_min'] = $speed;
            }
            $result['speed_sum'] += $speed;

            if (isset(\App\App::SHIP_TYPE_HASH[$ship->type])) {
                $type = \App\App::SHIP_TYPE_HASH[$ship->type];
                if (!isset($type['group'])) {
                    continue;
                }

                if (!isset($group_speed[$type['group']])) {
                    $group_speed[$type['group']] = [
                        'count' => 0,
                        'speed_sum' => 0,
                    ];
                }

                $group_speed[$type['group']]['count'] ++;
                $group_speed[$type['group']]['speed_sum'] += $speed;
            }
        }
        $result['speed_avg'] = round($result['speed_sum'] / $result['count'], 2);

        $result['speed_avg_str'] = $result['speed_sum'] . '/' . $result['count'] . '=' . $result['speed_avg'];

        $min_speed_type = '';
        $min_speed = 0;

        foreach ($group_speed as $type => $info) {
            $info['speed_avg'] = round($info['speed_sum'] / $info['count'], 2);
            $info['speed_avg_str'] = $info['speed_sum'] . '/' . $info['count'] . '=' . $info['speed_avg'];
            $group_speed[$type] = $info;

            $result['speed_avg_str_' . $type] = $info['speed_avg_str'];

            if ($min_speed_type == '' ||
                    $min_speed_type == 'sub' ||
                    ($type != 'sub' && $info['speed_avg'] < $min_speed)) {
                $min_speed_type = $type;
                $min_speed = $info['speed_avg'];
                $result['fleet_speed_str'] = $info['speed_avg_str'];
            }
        }

        return $result;
    }

}
