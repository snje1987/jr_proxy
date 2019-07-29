<?php

namespace App\Model;

class PlayerInfo {

    const DATA_DIR = APP_DATA_DIR . '/player_info/';

    protected $file;
    protected $uid;
    protected $ship_list = [];

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
            'title' => $info['title'],
            'cid' => $info['shipCid'],
            'isLocked' => $info['isLocked'],
            'strengthenAttribute' => $info['strengthenAttribute'],
            'equipment' => $info['equipment'],
        ];
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

}
