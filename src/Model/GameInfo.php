<?php

namespace App\Model;

class GameInfo {

    protected static $instance = null;
    protected static $timestamp = 0;

    const GAME_VERSION = '4.5.0';
    const GAME_INFO_URL = 'http://login.jr.moefantasy.com:80/index/getInitConfigs/';
    const DATA_FILE = APP_DATA_DIR . '/game_info.json';

    protected $file;
    protected $data_version = '';
    protected $app_version = '';
    protected $ship_cards = [];

    public static function get_game_info() {
        $url = self::GAME_INFO_URL . '&t=' . time() . str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT) . '&gz=1&market=2&channel=100016&version=' . self::GAME_VERSION;
        $data = file_get_contents($url);

        $data = @zlib_decode($data);
        if ($data === false) {
            return;
        }

        $json = json_decode($data, true);
        if ($json === null) {
            return;
        }

        return $json;
    }

    /**
     * @return self
     */
    public static function get() {

        if (file_exists(self::DATA_FILE)) {
            $mtime = filemtime(self::DATA_FILE);
        }
        else {
            $mtime = 0;
        }

        if (self::$instance === null) {
            self::$instance = new self();
        }

        if (self::$timestamp != $mtime) {
            self::$instance->load_info();
            self::$timestamp = $mtime;
        }

        return self::$instance;
    }

    public function update_info() {
        $json = self::get_game_info();

        if (!isset($json['DataVersion'])) {
            return;
        }

        if (isset($json['shipCardWu'])) {
            $ship_card = $json['shipCardWu'];
        }
        elseif (isset($json['shipCard'])) {
            $ship_card = $json['shipCard'];
        }
        else {
            return;
        }

        $this->data_version = $json['DataVersion'];
        $this->app_version = \App\App::APP_VERSION;

        $this->ship_cards = [];

        foreach ($ship_card as $ship) {
            if ($ship['npc'] != 0) {
                continue;
            }
            $id = $ship['cid'];

            $this->ship_cards[$id] = [
                'title' => $ship['title'],
                'dismantle' => $ship['dismantle'], //拆解
                'strengthenTop' => $ship['strengthenTop'], //强化满的消耗
                'strengthenLevelUpExp' => $ship['strengthenLevelUpExp'], //每级花费点数
                'strengthenSupplyExp' => $ship['strengthenSupplyExp'], //狗粮口感
            ];
        }

        $this->save_info();
    }

    public function update_check($new_data_version) {
        if ($this->app_version !== \App\App::APP_VERSION ||
                $this->data_version !== $new_data_version) {
            $this->update_info();
        }
    }

    public function get_ship_card($cid) {
        if (isset($this->ship_cards[$cid])) {
            return $this->ship_cards[$cid];
        }
        return null;
    }

    ///////////////////////////

    protected function __construct() {
        
    }

    protected function save_info() {
        $data = [
            'data_version' => $this->data_version,
            'app_version' => $this->app_version,
            'ship_cards' => $this->ship_cards,
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        file_put_contents(self::DATA_FILE, $json);
    }

    protected function load_info() {
        if (file_exists(self::DATA_FILE)) {
            $json = file_get_contents(self::DATA_FILE);
            $data = json_decode($json, true);
        }
        else {
            $data = [];
        }

        $this->data_version = isset($data['data_version']) ? strval($data['data_version']) : '';
        $this->app_version = isset($data['app_version']) ? strval($data['app_version']) : '';
        $this->ship_cards = isset($data['ship_cards']) ? $data['ship_cards'] : [];
    }

}
