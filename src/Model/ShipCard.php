<?php

namespace App\Model;

class ShipCard {

    protected $file;
    protected $list = [];

    public function __construct() {
        $this->file = APP_DATA_DIR . '/ship_card.json';

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

    public function get_ship($cid) {
        if (isset($this->list[$cid])) {
            return $this->list[$cid];
        }
        return null;
    }

}
