<?php

namespace App\Model;

use Exception;
use App\Model\CurrentWar;

class Fleet {

    protected $id;
    protected $title;
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

    public function get_ships() {
        return $this->ships;
    }

    public function get_fleet_card() {
        if (empty($this->ships)) {
            return [];
        }
        $result = [];

        $result['count'] = count($this->ships);
        $result['speed_flag_ship'] = $this->ships[0]->battle_props['speed'];
        $result['speed_max'] = $this->ships[0]->battle_props['speed'];
        $result['speed_min'] = $this->ships[0]->battle_props['speed'];
        $result['speed_sum'] = 0;

        $group_speed = [];

        foreach ($this->ships as $ship) {
            if ($ship->battle_props['speed'] > $result['speed_max']) {
                $result['speed_max'] = $ship->battle_props['speed'];
            }
            if ($ship->battle_props['speed'] < $result['speed_min']) {
                $result['speed_min'] = $ship->battle_props['speed'];
            }
            $result['speed_sum'] += $ship->battle_props['speed'];

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
                $group_speed[$type['group']]['speed_sum'] += $ship->battle_props['speed'];
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

    /////////////////////
    protected $game_info;

}
