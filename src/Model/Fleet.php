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

        foreach ($this->ships as $ship) {
            if ($ship['speed'] > $result['speed_max']) {
                $result['speed_max'] = $ship['speed'];
            }
            if ($ship['speed'] < $result['speed_min']) {
                $result['speed_min'] = $ship['speed'];
            }
            $result['speed_sum'] += $ship['speed'];
        }
        $result['speed_avg'] = round($result['speed_sum'] / $result['count'], 2);

        $result['speed_avg_str'] = $result['speed_sum'] . '/' . $result['count'] . '=' . $result['speed_avg'];

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

        $result['type'] = \App\App::SHIP_TYPE_HASH[$ship['type']];
        $result['isLocked'] = $ship['isLocked'] == 1 ? '是' : '否';
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
        foreach ($ship['tactics_avl'] as $tid) {
            $card = $this->game_info->get_tactics_card($tid);
            if ($card !== null) {
                $list[] = $card;
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
            }
        }
        $result['equipment'] = $equip_list;

        foreach ($ship['capacitySlotExist'] as $k => $exist) {
            if ($exist == 1) {
                $result['equipment'][$k]['num'] = $ship['capacitySlot'][$k];
                $result['equipment'][$k]['max'] = $ship['capacitySlotMax'][$k];
            }
        }

        foreach ($ship['missileSlotExist'] as $k => $exist) {
            if ($exist == 1) {
                $result['equipment'][$k]['num'] = $ship['missileSlot'][$k];
                $result['equipment'][$k]['max'] = $ship['missileSlotMax'][$k];
            }
        }

        return $result;
    }

    /////////////////////
    protected $game_info;

}
