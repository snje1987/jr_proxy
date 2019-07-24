<?php

namespace App\Model;

class GameInfo {

    protected static $instance = null;
    protected static $timestamp = 0;

    const GAME_INFO_URL = 'http://login.jr.moefantasy.com:80/index/getInitConfigs/';
    const DATA_FILE = APP_DATA_DIR . '/game_info.json';

    protected $file;
    protected $data_version = '';
    protected $app_version = '';
    protected $game_version = '';
    protected $ship_cards = []; //舰船
    protected $equip_cards = []; //装备
    protected $skill_cards = []; //技能
    protected $tactics_cards = []; //战术

    public function get_game_info() {
        if ($this->game_version == '') {
            return;
        }
        $url = self::GAME_INFO_URL . '&t=' . time() . str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT) . '&gz=1&market=2&channel=100016&version=' . $this->game_version;
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

    public function update_info($new_game_version) {
        $this->game_version = $new_game_version;
        
        $json = self::get_game_info();

        if (!isset($json['DataVersion'])) {
            return;
        }

        $this->data_version = $json['DataVersion'];
        $this->app_version = \App\App::APP_VERSION;

        if (isset($json['shipCardWu'])) {
            $this->build_ship_cards($json['shipCardWu']);
        }
        elseif (isset($json['shipCard'])) {
            $$this->build_ship_cards($json['shipCardWu']);
        }

        if (isset($json['shipEquipmnt'])) {
            $this->build_equip_cards($json['shipEquipmnt']);
        }

        if (isset($json['shipSkil1'])) {
            $this->build_skill_cards($json['shipSkil1']);
        }

        if (isset($json['ShipTactics'])) {
            $this->build_tactics_cards($json['ShipTactics']);
        }

        $this->save_info();
    }

    public function update_check($new_game_version, $new_data_version) {
        if ($this->app_version !== \App\App::APP_VERSION ||
                $this->data_version !== $new_data_version ||
                $this->game_version !== $new_game_version
        ) {
            $this->update_info($new_game_version);
        }
    }

    public function get_ship_card($cid) {
        if (isset($this->ship_cards[$cid])) {
            return $this->ship_cards[$cid];
        }
        return null;
    }

    ///////////////////////////

    protected function build_ship_cards($list) {
        $this->ship_cards = [];

        foreach ($list as $ship) {
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
    }

    protected function build_equip_cards($list) {
        $this->equip_cards = [];
        foreach ($list as $equip) {
            $id = $equip['cid'];
            $this->equip_cards[$id] = [
                'title' => $equip['title'],
            ];
        }
    }

    protected function build_skill_cards($list) {
        $this->skill_cards = [];
        foreach ($list as $skill) {
            $id = $skill['skillId'];
            $this->skill_cards[$id] = [
                'title' => $skill['title'],
            ];
        }
    }

    protected function build_tactics_cards($list) {
        $this->tactics_cards = [];
        foreach ($list as $tactic) {
            $tid = $tactic['tacticsId'];
            if (isset($this->tactics_cards[$tid])) {
                continue;
            }
            $this->tactics_cards[$tid] = [
                'title' => $tactic['title'],
            ];
        }
    }

    protected function __construct() {
        
    }

    protected function save_info() {
        $data = [
            'data_version' => $this->data_version,
            'app_version' => $this->app_version,
            'ship_cards' => $this->ship_cards,
            'game_version' => $this->game_version,
            'equip_cards' => $this->equip_cards,
            'skill_cards' => $this->skill_cards,
            'tactics_cards' => $this->tactics_cards,
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
        $this->game_version = isset($data['game_version']) ? strval($data['game_version']) : '';
        $this->ship_cards = isset($data['ship_cards']) ? $data['ship_cards'] : [];
        $this->equip_cards = isset($data['equip_cards']) ? $data['equip_cards'] : [];
        $this->skill_cards = isset($data['skill_cards']) ? $data['skill_cards'] : [];
        $this->tactics_cards = isset($data['tactics_cards']) ? $data['tactics_cards'] : [];
    }

}
