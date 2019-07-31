<?php

namespace App\Model;

use Exception;
use JsonSerializable;

class PlayerInfo implements JsonSerializable {

    const DATA_DIR = APP_DATA_DIR . '/player_info/';

    protected $file;
    protected $uid;
    protected $ship_list = [];
    protected $fleet_list = [];

    public function __construct($uid, $noload = false) {
        $this->uid = $uid;

        if (!file_exists(self::DATA_DIR)) {
            mkdir(self::DATA_DIR, 0777, true);
        }

        $this->file = self::DATA_DIR . $this->uid . '.json';
        $this->game_info = GameInfo::get();

        if (!$noload) {
            $this->load();
        }
    }

    public function save() {
        $json = json_encode($this, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

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

        $ship_list = isset($data['ship_list']) ? $data['ship_list'] : [];
        $this->fleet_list = isset($data['fleet_list']) ? $data['fleet_list'] : [];

        $this->ship_list = [];
        foreach ($ship_list as $v) {
            $id = $v['id'];
            try {
                $ship = new Ship();
                $ship->init_from_save($v);
                $this->ship_list[$id] = $ship;
            }
            catch (Exception $ex) {
                
            }
        }

        return $this;
    }

    public function set_tactics($list) {
        foreach ($list as $v) {
            if (!isset($this->ship_list[$v['boat_id']])) {
                continue;
            }
            $this->ship_list[$v['boat_id']]->set_tactic($v['tactics_id'], $v['cid']);
        }

        return $this;
    }

    public function set_fleet($list) {
        foreach ($list as $v) {
            $id = $v['id'];
            $this->fleet_list[$id] = [
                'id' => $id,
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
        try {
            $id = $info['id'];

            if (isset($this->ship_list[$id])) {
                $ship = $this->ship_list[$id];
            }
            else {
                $ship = new Ship();
            }

            $ship->init_from_api($info);
            $this->ship_list[$id] = $ship;
        }
        catch (Exception $ex) {
            
        }
        return $this;
    }

    public function update_ships_res($list) {
        foreach ($list as $v) {
            $this->update_ship_res($v);
        }
        return $this;
    }

    public function update_ship_res($info) {
        $id = $info['id'];
        if (!isset($this->ship_list[$id])) {
            return $this;
        }
        $ship = $this->ship_list[$id];

        $ship->update_res($info);

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

    public function get_ship($id) {
        if (!isset($this->ship_list[$id])) {
            return null;
        }
        return $this->ship_list[$id];
    }

    public function get_need_strengthen_ships() {
        $list = [];

        foreach ($this->ship_list as $id => $ship) {
            if ($ship->is_locked == 1 && $ship->need_strengthen()) {
                $list[$id] = $ship;
            }
        }
        ksort($list);

        return $list;
    }

    public function get_material_ships($cid = null) {
        $game_info = GameInfo::get();

        $list = [];

        foreach ($this->ship_list as $id => $ship) {
            if ($ship->is_locked == 0) {
                if (!isset($list[$ship->ship_cid])) {

                    if (is_array($cid) && !in_array($ship->ship_cid, $cid)) {
                        continue;
                    }

                    $card = $game_info->get_ship_card($ship->ship_cid);

                    if ($card === null) {
                        continue;
                    }

                    $list[$ship->ship_cid] = [
                        'count' => 1,
                        'title' => $ship->title,
                        'strengthen_supply' => $card['strengthenSupplyExp'],
                        'dismantle' => $card['dismantle'],
                    ];
                }
                else {
                    $list[$ship->ship_cid]['count'] ++;
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
        if (!isset($this->fleet_list[$id])) {
            return null;
        }

        $fleet_info = $this->fleet_list[$id];

        $fleet = new Fleet($id, $fleet_info['title']);

        $ship_list = [];
        foreach ($fleet_info['ships'] as $k => $id) {
            $ship_list[$k] = $this->ship_list[$id];
        }
        $fleet->set_ships($ship_list);

        return $fleet;
    }

    ///////////////////////////////////
    protected $game_info;

    ///////////////////////////////////
    public function jsonSerialize() {
        ksort($this->ship_list);
        return [
            'ship_list' => $this->ship_list,
            'fleet_list' => $this->fleet_list,
        ];
    }

}
