<?php

namespace App\Model;

class ShipList {

    protected $file;
    protected $list = [];

    public function __construct() {
        $dir = APP_ROOT . \App\Config::get('main', 'tmp', '/tmp');

        $this->file = $dir . '/ship_list.json';

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

}
