<?php

namespace App\Model;

use Exception;
use App\Model\CurrentWar;

class Fleet {

    protected $ships;
    protected $ship_cards;

    public function __construct() {
        $this->game_info = GameInfo::get();
    }

    public function set_ships($ships) {
        $this->ships = $ships;
        $this->update_ship_cards();
    }

    public function get_ship_cards() {
        return $this->ship_cards;
    }

    public function get_fleet_card() {
        if (empty($this->ships)) {
            return [];
        }
        $result = [];

        $result['count'] = count($this->ships);
        $result['speed_flag_ship'] = $this->ships[0]['speed'];
        $result['speed_max'] = $this->ships[0]['speed'];
        $result['speed_min'] = $this->ships[0]['speed'];
        $result['speed_sum'] = 0;

        $group_speed = [];

        foreach ($this->ships as $ship) {
            if ($ship['speed'] > $result['speed_max']) {
                $result['speed_max'] = $ship['speed'];
            }
            if ($ship['speed'] < $result['speed_min']) {
                $result['speed_min'] = $ship['speed'];
            }
            $result['speed_sum'] += $ship['speed'];

            if (isset(\App\App::SHIP_TYPE_HASH[$ship['type']])) {
                $type = \App\App::SHIP_TYPE_HASH[$ship['type']];
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
                $group_speed[$type['group']]['speed_sum'] += $ship['speed'];
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

    ////////////////////////////

    protected function update_ship_cards() {
        $this->ship_cards = [];
        foreach ($this->ships as $ship) {
            $this->ship_cards[] = $this->make_ship_card($ship);
        }
    }

    protected function make_ship_card($ship) {
        $result = $ship;

        $result['range'] = \App\App::RANGE_NAME[$ship['range']];

        $result['type'] = \App\App::SHIP_TYPE_HASH[$ship['type']]['title'];
        if (isset($ship['isLocked'])) {
            $result['isLocked'] = $ship['isLocked'] == 1 ? '是' : '否';
        }
        $result['married'] = $ship['married'] == 1 ? '是' : '否';

        $ship_card = $this->game_info->get_ship_card($result['shipCid']);

        if ($ship_card !== null) {
            $result['ori_title'] = $ship_card['title'];
            $country = $ship_card['country'];
            $result['country'] = \App\App::COUNTRY_NAME[$country];
            $result['shipIndex'] = $ship_card['shipIndex'];
            $result['evoClass'] = $ship_card['evoClass'] > 0 ? '改' . $ship_card['evoClass'] : '未改';
        }

        $list = [];
        foreach ($ship['tactics'] as $tid) {
            $card = $this->game_info->get_tactics_card($tid);
            if ($card !== null) {
                $list[] = $card;
            }
        }
        $result['tactics'] = $list;

        $list = [];
        if (isset($ship['tactics_avl'])) {
            foreach ($ship['tactics_avl'] as $tid) {
                $card = $this->game_info->get_tactics_card($tid);
                if ($card !== null) {
                    $list[] = $card;
                }
            }
        }
        $result['tactics_avl'] = $list;

        if ($ship['skillId'] != 0) {
            $skill = $this->game_info->get_skill_card($ship['skillId']);
            if ($skill !== null) {
                $result['skill'] = $skill;
            }
        }
        unset($result['skillId']);

        $equip_list = [];
        foreach ($ship['equipment'] as $eid) {
            $equip = $this->game_info->get_equip_card($eid);
            if ($equip !== null) {
                $equip_list[] = $equip;

                foreach (\App\App::SHIP_BATTLE_PROP_NAME as $k => $v) {
                    if (!isset($equip[$k]) || !isset($result[$k]) || $k == 'range') {
                        continue;
                    }

                    if (!is_array($result[$k])) {
                        $result[$k] = [$result[$k], $equip[$k]];
                    }
                    else {
                        $result[$k][1] += $equip[$k];
                    }
                }

                foreach (\App\App::SHIP_RES_NAME as $k => $v) {
                    if (!isset($equip[$k]) || !isset($result[$k])) {
                        continue;
                    }

                    if (!is_array($result[$k . '_max'])) {
                        $result[$k . '_max'] = [$result[$k . '_max'], $equip[$k]];
                    }
                    else {
                        $result[$k . '_max'][1] += $equip[$k];
                    }
                }
            }
            else {
                $equip_list[] = null;
            }
        }
        $result['equipment'] = $equip_list;

        foreach ($result['equipment'] as $k => $equip) {
            if ($equip === null) {
                continue;
            }
            if (!isset($equip['aluminiumUse']) || $equip['aluminiumUse'] <= 0) {
                continue;
            }

            $check_names = ['capacitySlot', 'missileSlot'];

            foreach ($check_names as $check_name) {
                if (!isset($ship[$check_name])) {
                    continue;
                }

                if (!isset($ship[$check_name . 'Exist']) ||
                        (isset($ship[$check_name . 'Exist'][$k]) && $ship[$check_name . 'Exist'][$k] == 1)) {
                    if ($ship[$check_name . 'Max'][$k] > 0) {
                        $equip['num'] = $ship[$check_name][$k];
                        $equip['max'] = $ship[$check_name . 'Max'][$k];
                        break;
                    }
                }
            }

            $result['equipment'][$k] = $equip;
        }

        return $result;
    }

    /////////////////////
    protected $game_info;

}
