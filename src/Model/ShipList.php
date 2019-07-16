<?php

namespace App\Model;

class ShipList {

    protected $file;
    protected $list = [];

    public function __construct() {
        $this->file = APP_TMP_DIR . '/ship_list.json';

        $this->load_list();
    }

    public function set_list($list) {
        $this->list = $list;
        $this->save_list();
    }

    public function save_list() {
        ksort($this->list);

        $json = json_encode($this->list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        file_put_contents($this->file, $json);
    }

    public function load_list() {
        if (file_exists($this->file)) {
            $json = file_get_contents($this->file);
            $this->list = json_decode($json, true);
        }
        else {
            $this->list = [];
        }
    }

    public function get_list() {
        return $this->list;
    }

    public function set_ship($id, $ship) {
        $this->list[$id] = $ship;

        $this->save_list();
    }

    public function del_ships($ids) {
        foreach ($ids as $id) {
            if (isset($this->list[$id])) {
                unset($this->list[$id]);
            }
        }
        $this->save_list();
    }

    public function get_target($id) {
        if (!isset($this->list[$id])) {
            return null;
        }

        $ship = $this->list[$id];
        if ($ship['isLocked'] != 1) {
            return null;
        }

        $ship_card = new ShipCard();

        $card = $ship_card->get_ship($ship['shipCid']);
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

    public function get_target_list() {

        $ship_card = new ShipCard();

        $list = [];

        foreach ($this->list as $id => $v) {
            if ($v['isLocked'] == 1) {
                $card = $ship_card->get_ship($v['shipCid']);
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

    public function get_material($cid = null) {
        $ship_card = new ShipCard();

        $list = [];

        foreach ($this->list as $id => $v) {
            if ($v['isLocked'] == 0) {
                if (!isset($list[$v['shipCid']])) {

                    if (is_array($cid) && !in_array($v['shipCid'], $cid)) {
                        continue;
                    }

                    $card = $ship_card->get_ship($v['shipCid']);

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
