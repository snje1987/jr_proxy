<?php

namespace App\Model;

use Exception;
use JsonSerializable;

class Ship implements JsonSerializable {

    protected $id;
    protected $title;
    protected $level;
    protected $ship_cid;
    protected $is_locked;
    protected $type;
    protected $love;
    protected $married;
    ////////////////
    protected $battle_props = [];
    protected $res = [];
    protected $strengthen = [];
    protected $equipment = [];
    protected $tactics = [];
    protected $tacitcs_in_use = [];
    protected $skill = [];
    /////////////////
    protected $ori_title;
    protected $country;
    protected $strengthen_top = [];
    protected $extra_attr;
    protected $ship_index;
    protected $evo_class;

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

    public function __construct() {
        
    }

    public function init_from_api($ship_info) {
        $this->init_attr($ship_info);
        $this->strengthen = $ship_info['strengthenAttribute'];

        if (isset($ship_info['skillId']) && $ship_info['skillId'] > 0) {
            $card = self::$game_info->get_skill_card($ship_info['skillId']);
            if ($card !== null) {
                $this->skill = $card;
            }
        }

        $this->tacitcs_in_use = $ship_info['tactics'];

        $this->init_battle_props($ship_info);
        $this->init_equip($ship_info);
    }

    public function init_from_save($ship_info) {
        $this->init_attr($ship_info, false);
        $this->strengthen = $ship_info['strengthen'];

        $this->battle_props = $ship_info['battle_props'];
        $this->res = $ship_info['res'];
        $this->tacitcs_in_use = $ship_info['tacitcs_in_use'];

        foreach ($ship_info['tactics'] as $tid => $cid) {
            $card = self::$game_info->get_tactics_card($cid);
            if ($card !== null) {
                $this->tactics[$tid] = $card;
            }
        }

        if ($ship_info['skill'] > 0) {
            $card = self::$game_info->get_skill_card($ship_info['skill']);
            if ($card !== null) {
                $this->skill = $card;
            }
        }

        foreach ($ship_info['equipment'] as $k => $v) {
            if (is_array($v)) {
                $eid = $v['id'];
            }
            else {
                $eid = $v;
            }

            $equip_card = self::$game_info->get_equip_card($eid);
            $this->equipment[$k] = $equip_card;
            if ($equip_card !== null) {
                if (is_array($v)) {
                    $this->equipment[$k]['max'] = $v['max'];
                    $this->equipment[$k]['num'] = $v['num'];
                }
            }
        }
    }

    public function init_from_warlog($ship_info) {

        $this->init_attr($ship_info);
        $this->strengthen = [];

        $this->battle_props = [];
        foreach (\App\App::SHIP_BATTLE_PROP_NAME as $k => $v) {
            if (isset($ship_info[$k])) {
                $this->battle_props[$k] = $ship_info[$k];
            }
        }

        foreach (\App\App::SHIP_RES_NAME as $k => $v) {
            if (isset($ship_info[$k])) {
                $this->res[$k] = $ship_info[$k];
                $this->res[$k . '_max'] = $ship_info[$k . 'Max'];
            }
        }

        foreach ($ship_info['tactics'] as $cid) {
            if ($cid > 0) {
                $card = self::$game_info->get_tactics_card($cid);
                $this->tactics[$card['tid']] = $card;
                $this->tacitcs_in_use = $card['tid'];
            }
        }

        if (isset($ship_info['skillId']) && $ship_info['skillId'] > 0) {
            $card = self::$game_info->get_skill_card($ship_info['skillId']);
            if ($card !== null) {
                $this->skill = $card;
            }
        }

        $this->init_equip($ship_info);
    }

    public function update_res($info) {
        $this->res['oil'] = $info['oil'];
        $this->res['ammo'] = $info['ammo'];

        foreach ($this->equipment as $k => $v) {
            if ($info['capacitySlot'][$k] > 0) {
                $this->equipment['num'] = $info['capacitySlot'][$k];
            }
            elseif ($info['missileSlot'][$k] > 0) {
                $this->equipment['num'] = $info['missileSlot'][$k];
            }
        }
    }

    public function set_tactic($tid, $cid) {
        $card = self::$game_info->get_tactics_card($cid);
        if ($card !== null) {
            $this->tactics[$tid] = $card;
        }
    }

    public function need_strengthen() {
        foreach ($this->strengthen as $k => $v) {
            if ($v < $this->strengthen_top[$k]) {
                return true;
            }
        }
        return false;
    }

    public function get_ship_card() {

        $result = [
            'id' => $this->id,
            'title' => $this->title,
            'level' => $this->level,
            'ship_cid' => $this->ship_cid,
            'type' => $this->type,
            'love' => $this->love,
            'ori_title' => $this->ori_title,
            'ship_index' => $this->ship_index,
            'skill' => $this->skill,
            'tactics' => $this->tactics,
            'equipment' => $this->equipment,
        ];

        foreach ($this->battle_props as $k => $v) {
            $result[$k] = $v;
        }

        foreach ($this->res as $k => $v) {
            $result[$k] = $v;
        }

        $result['range'] = \App\App::RANGE_NAME[$this->battle_props['range']];
        $result['type'] = \App\App::SHIP_TYPE_HASH[$this->type]['title'];

        if (isset($this->is_locked)) {
            $result['is_locked'] = $this->is_locked == 1 ? '是' : '否';
        }
        $result['married'] = $this->married == 1 ? '是' : '否';
        if (isset(\App\App::COUNTRY_NAME[$this->country])) {
            $result['country'] = \App\App::COUNTRY_NAME[$this->country];
        }
        $result['evo_class'] = $this->evo_class > 0 ? '改' . $this->evo_class : '未改';

        foreach ($this->tacitcs_in_use as $tid) {
            if ($tid > 0) {
                $result['tactics'][$tid]['in_use'] = true;
            }
        }

        foreach ($this->equipment as $equip) {
            if ($equip !== null) {
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
        }

        return $result;
    }

    /////////////////////////////////////////
    protected function init_attr($ship_info, $trans = true) {
        $attrs = [
            'id' => 'id',
            'title' => 'title',
            'level' => 'level',
            'ship_cid' => 'shipCid',
            'is_locked' => 'isLocked',
            'type' => 'type',
            'love' => 'love',
            'married' => 'married',
        ];

        foreach ($attrs as $k => $v) {
            if (!$trans) {
                $v = $k;
            }
            if (isset($ship_info[$v])) {
                $this->{$k} = $ship_info[$v];
            }
            else {
                $this->{$k} = 0;
            }
        }

        $card = self::$game_info->get_ship_card($this->ship_cid);
        if ($card !== null) {
            $this->ori_title = $card['title'];
            $this->country = $card['country'];
            $this->strengthen_top = $card['strengthenTop'];
            $this->ship_index = $card['shipIndex'];
            $this->evo_class = $card['evoClass'];
        }
    }

    protected function init_battle_props($ship_info) {
        $this->battle_props = [];
        foreach (\App\App::SHIP_BATTLE_PROP_NAME as $k => $v) {
            $this->battle_props[$k] = $ship_info['battlePropsBasic'][$k];
        }

        foreach (\App\App::SHIP_RES_NAME as $k => $v) {
            $this->res[$k] = $ship_info['battleProps'][$k];
            $this->res[$k . '_max'] = $ship_info['battlePropsMax'][$k];
        }
    }

    protected function init_equip($ship_info) {
        $this->equipment = [];

        foreach ($ship_info['equipment'] as $k => $eid) {
            $equip_card = self::$game_info->get_equip_card($eid);
            $this->equipment[$k] = $equip_card;

            if ($equip_card !== null) {
                if (!isset($equip_card['aluminiumUse']) || $equip_card['aluminiumUse'] <= 0) {
                    continue;
                }

                $check_names = ['capacitySlot', 'missileSlot'];

                foreach ($check_names as $check_name) {
                    if (!isset($ship_info[$check_name])) {
                        continue;
                    }

                    if (isset($ship_info[$check_name . 'Exist'][$k]) && $ship_info[$check_name . 'Exist'][$k] == 1) {
                        if ($ship_info[$check_name . 'Max'][$k] > 0) {
                            $this->equipment[$k]['max'] = $ship_info[$check_name . 'Max'][$k];
                            $this->equipment[$k]['num'] = $ship_info[$check_name][$k];
                        }
                    }
                }
            }
        }

        ksort($this->equipment, SORT_NUMERIC);
    }

    /////////////////////////////////////////

    /**
     * @var GameInfo; 
     */
    protected static $game_info;

    public static function init_class() {
        self::$game_info = GameInfo::get();
    }

    public function jsonSerialize() {
        $ret = [
            'id' => $this->id,
            'title' => $this->title,
            'level' => $this->level,
            'ship_cid' => $this->ship_cid,
            'is_locked' => $this->is_locked,
            'type' => $this->type,
            'love' => $this->love,
            'married' => $this->married,
            'battle_props' => $this->battle_props,
            'res' => $this->res,
            'strengthen' => $this->strengthen,
            'tacitcs_in_use' => $this->tacitcs_in_use,
        ];

        $ret['equipment'] = [];
        foreach ($this->equipment as $k => $v) {
            if (empty($v)) {
                $ret['equipment'][$k] = 0;
            }
            elseif (isset($v['max'])) {
                $ret['equipment'][$k] = [
                    'id' => $v['id'],
                    'num' => $v['num'],
                    'max' => $v['max'],
                ];
            }
            else {
                $ret['equipment'][$k] = $v['id'];
            }
        }

        $ret['tactics'] = [];
        foreach ($this->tactics as $tid => $v) {
            $ret['tactics'][$tid] = $v['id'];
        }

        if (!empty($this->skill)) {
            $ret['skill'] = $this->skill['id'];
        }
        else {
            $ret['skill'] = 0;
        }

        return $ret;
    }

}

Ship::init_class();
