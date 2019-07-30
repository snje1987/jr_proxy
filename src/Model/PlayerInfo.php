<?php

namespace App\Model;

class PlayerInfo {

    const DATA_DIR = APP_DATA_DIR . '/player_info/';

    protected $file;
    protected $uid;
    protected $ship_list = [];
    protected $tactics_list = [];
    protected $fleet_list = [];

    public function __construct($uid, $noload = false) {
        $this->uid = $uid;

        if (!file_exists(self::DATA_DIR)) {
            mkdir(self::DATA_DIR, 0777, true);
        }

        $this->file = self::DATA_DIR . $this->uid . '.json';

        if (!$noload) {
            $this->load();
        }
    }

    public function save() {
        ksort($this->ship_list);
        $data = [
            'ship_list' => $this->ship_list,
            'tactics_list' => $this->tactics_list,
            'fleet_list' => $this->fleet_list,
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        file_put_contents($this->file, $json);

        return $this;
    }

    public function load() {
        if (file_exists($this->file)) {
            $json = file_get_contents($this->file);
            $data = json_decode($json, true);
        }
        else {
            $data = [];
        }

        $this->ship_list = isset($data['ship_list']) ? $data['ship_list'] : [];
        $this->tactics_list = isset($data['tactics_list']) ? $data['tactics_list'] : [];
        $this->fleet_list = isset($data['fleet_list']) ? $data['fleet_list'] : [];

        return $this;
    }

    public function set_tactics($list) {
        foreach ($list as $v) {
            if (!isset($this->tactics_list[$v['boat_id']])) {
                $this->tactics_list[$v['boat_id']] = [];
            }
            $this->tactics_list[$v['boat_id']][$v['tactics_id']] = $v['cid'];
        }

        return $this;
    }

    public function set_fleet($list) {
        foreach ($list as $v) {
            $id = $v['id'];
            $this->fleet_list[$id] = [
                'title' => $v['title'],
                'ships' => $v['ships'],
            ];
        }

        return $this;
    }

    public function set_ships($list) {
        foreach ($list as $v) {
            $this->set_ship($v);
        }
        return $this;
    }

    public function set_ship($info) {
        $id = $info['id'];
        $ship = [
            'id' => $id,
            'title' => $info['title'], //名称
            'level' => $info['level'], //等级
            'shipCid' => $info['shipCid'], //CID
            'isLocked' => $info['isLocked'], //是否锁定
            'type' => $info['type'], //类型
            'love' => $info['love'], //好感
            'married' => $info['married'], //婚否
            'strengthenAttribute' => $info['strengthenAttribute'], //强化情况
            'equipment' => $info['equipment'], //装备
            'capacitySlot' => $info['capacitySlot'], //当前飞机搭载数
            'capacitySlotMax' => $info['capacitySlotMax'], //当前飞机搭载数
            'capacitySlotExist' => $info['capacitySlotExist'], //当前飞机搭载数
            'missileSlot' => $info['missileSlot'], //导弹搭载数
            'missileSlotMax' => $info['missileSlotMax'], //导弹搭载数
            'missileSlotExist' => $info['missileSlotExist'], //导弹搭载数
            'tactics' => $info['tactics'],
            'battleProps' => self::get_battle_props($info), //战斗属性
        ];
        if (isset($info['skillId'])) {
            $ship['skillId'] = $info['skillId']; //技能ID
        }
        else {
            $ship['skillId'] = 0;
        }

        $this->ship_list[$id] = $ship;

        return $this;
    }

    public function del_ships($ids) {
        foreach ($ids as $id) {
            if (isset($this->ship_list[$id])) {
                unset($this->ship_list[$id]);
            }
        }

        return $this;
    }

    public function get_target_ship($id) {
        if (!isset($this->ship_list[$id])) {
            return null;
        }

        $ship = $this->ship_list[$id];
        if ($ship['isLocked'] != 1) {
            return null;
        }

        $game_info = GameInfo::get();

        $card = $game_info->get_ship_card($ship['shipCid']);
        if ($card === null) {
            return null;
        }

        $cur_strengthen = $ship['strengthenAttribute'];
        $full_strengthen = $card['strengthenTop'];

        foreach ($cur_strengthen as $k1 => $v1) {
            if ($v1 < $full_strengthen[$k1]) {
                $ship['strengthenTop'] = $full_strengthen;
                return $ship;
            }
        }

        return null;
    }

    public function get_target_ships() {

        $game_info = GameInfo::get();

        $list = [];

        foreach ($this->ship_list as $id => $v) {
            if ($v['isLocked'] == 1) {
                $card = $game_info->get_ship_card($v['shipCid']);
                if ($card !== null) {
                    $cur_strengthen = $v['strengthenAttribute'];
                    $full_strengthen = $card['strengthenTop'];

                    foreach ($cur_strengthen as $k1 => $v1) {
                        if ($v1 < $full_strengthen[$k1]) {
                            $v['strengthenTop'] = $full_strengthen;
                            $list[$id] = $v;
                            break;
                        }
                    }
                }
            }
        }

        ksort($list);

        return $list;
    }

    public function get_material_ships($cid = null) {
        $game_info = GameInfo::get();

        $list = [];

        foreach ($this->ship_list as $id => $v) {
            if ($v['isLocked'] == 0) {
                if (!isset($list[$v['shipCid']])) {

                    if (is_array($cid) && !in_array($v['shipCid'], $cid)) {
                        continue;
                    }

                    $card = $game_info->get_ship_card($v['shipCid']);

                    if ($card === null) {
                        continue;
                    }

                    $list[$v['shipCid']] = [
                        'count' => 1,
                        'title' => $v['title'],
                        'strengthenSupplyExp' => $card['strengthenSupplyExp'],
                        'dismantle' => $card['dismantle'],
                    ];
                }
                else {
                    $list[$v['shipCid']]['count'] ++;
                }
            }
        }

        ksort($list);

        return $list;
    }

    public function get_fleet_list() {
        $list = [];
        foreach ($this->fleet_list as $id => $v) {
            $list[$id] = $v['title'];
        }

        return $list;
    }

    public function get_fleet($id) {
        if (isset($this->fleet_list[$id])) {
            return $this->fleet_list[$id];
        }
        return null;
    }

    public function get_ship_info($id) {
        if (!isset($this->ship_list[$id])) {
            return null;
        }

        $raw_info = $this->ship_list[$id];

        $ship_info = $raw_info;

        unset($ship_info['battleProps']);

        foreach ($raw_info['battleProps'] as $k => $v) {
            $ship_info[$k] = $v;
        }

        $tactics = [];
        if (isset($this->tactics_list[$raw_info['id']])) {
            $all_tactics = $this->tactics_list[$raw_info['id']];

            foreach ($raw_info['tactics'] as $tcid) {
                if ($tcid > 0) {
                    $tactics[] = $all_tactics[$tcid];
                    unset($all_tactics[$tcid]);
                }
            }

            $ship_info['tactics'] = $tactics;
            $ship_info['tactics_avl'] = [];

            foreach ($all_tactics as $tid) {
                $ship_info['tactics_avl'][] = $tid;
            }
        }
        else {
            $ship_info['tactics'] = [];
            $ship_info['tactics_avl'] = [];
        }

        return $ship_info;
    }

    ///////////////////////////////////

    protected static function get_battle_props($info) {
        $ret = [];
        foreach (\App\App::SHIP_BATTLE_PROP_NAME as $k => $v) {
            if ($k == 'hpMax') {
                $ret[$k] = $info['battlePropsMax']['hp'];
            }
            else {
                $ret[$k] = $info['battleProps'][$k];
            }
        }
        return $ret;
    }

}
