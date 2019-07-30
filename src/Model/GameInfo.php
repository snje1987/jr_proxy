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
    protected $buff_cards = []; //BUFF

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

        if (isset($json['shipSkillBuff'])) {
            $this->build_buff_cards($json['shipSkillBuff']);
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

    public function get_equip_card($cid) {
        if (isset($this->equip_cards[$cid])) {
            return $this->equip_cards[$cid];
        }
        return null;
    }

    public function get_skill_card($cid) {
        if (isset($this->skill_cards[$cid])) {
            return $this->skill_cards[$cid];
        }
        return null;
    }

    public function get_tactics_card($cid) {
        if (isset($this->tactics_cards[$cid])) {
            return $this->tactics_cards[$cid];
        }
        return null;
    }

    public function get_buff_card($cid) {
        if (isset($this->buff_cards[$cid])) {
            return $this->buff_cards[$cid];
        }
        return null;
    }

    ///////////////////////////

    protected static function clear_desc($str) {
        return preg_replace('/\^C[a-z0-9]{16}/i', '', $str);
    }

    protected function build_ship_cards($list) {
        $this->ship_cards = [];

        foreach ($list as $ship) {
            if ($ship['npc'] != 0) {
                continue;
            }
            $id = $ship['cid'];

            $this->ship_cards[$id] = [
                'title' => $ship['title'],
                'shipIndex' => $ship['shipIndex'], //图鉴编号
                'evoClass' => $ship['evoClass'], //改造
                'country' => $ship['country'], //国籍
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

            $this->equip_cards[$id] = $this->build_equip_info($equip);
        }
    }

    protected function build_skill_cards($list) {
        $this->skill_cards = [];
        foreach ($list as $skill) {
            $id = $skill['skillId'];
            $this->skill_cards[$id] = [
                'title' => $skill['title'],
                'level' => $skill['skillLevel'],
                'desc' => self::clear_desc($skill['desc']),
            ];
        }
    }

    protected function build_tactics_cards($list) {
        $this->tactics_cards = [];
        foreach ($list as $tactic) {
            $tid = $tactic['cid'];
            $this->tactics_cards[$tid] = [
                'title' => $tactic['title'],
                'level' => $tactic['level'],
                'desc' => self::clear_desc($tactic['desc']),
            ];
        }
    }

    protected function build_buff_cards($list) {
        $this->buff_cards = [];
        foreach ($list as $buff) {
            $cid = $buff['cid'];
            $this->buff_cards[$cid] = [
                'title' => $buff['title'],
                'level' => $buff['level'],
                'desc' => self::clear_desc($buff['desc']),
            ];
        }
    }

    protected function build_equip_info($equip) {
        $info = [];
        $info['title'] = $equip['title'];

        $desc = [];

        foreach (self::EQUIP_ATTR_NAME as $k => $v) {
            if (isset($equip[$k]) && $equip[$k] != 0) {
                $desc[] = $v . ':' . ($equip[$k] > 0 ? '+' . $equip[$k] : $equip[$k]);
                $info[$k] = $equip[$k];
            }
        }

        if ($equip['airDef'] != 0) {
            if (isset($equip['airDefCorrect']) && $equip['airDefCorrect'] != 0) {
                $desc[] = '对空补正:' . $equip['airDefCorrect'];
                $info['airDefCorrect'] = $equip['airDefCorrect'];
            }
            if (isset($equip['airDefRate']) && $equip['airDefRate'] != 0) {
                $desc[] = '对空倍率:' . $equip['airDefRate'];
                $info['airDefRate'] = $equip['airDefRate'];
            }
        }

        if (isset($equip['missileHit']) && $equip['missileHit'] != 0) {
            $desc[] = '突防:' . $equip['missileHit'];
            $info['missileHit'] = $equip['missileHit'];
        }

        if (isset($equip['aluminiumUse']) && $equip['aluminiumUse'] != 0) {
            $desc[] = '铝耗:' . $equip['aluminiumUse'];
            $info['aluminiumUse'] = $equip['aluminiumUse'];
        }

        if (isset($equip['range']) && $equip['range'] > 1) {
            $desc[] = '射程:' . self::EQUIP_RANGE_NAME[$equip['range']];
            $info['range'] = $equip['range'];
        }

        $names = ['desc', 'desc2'];
        foreach ($names as $name) {
            $str = preg_replace('/铝耗\d+/', '', $equip[$name]);
            if (empty($str)) {
                continue;
            }

            $desc[] = $str;
        }

        $info['desc'] = implode('&#10;', $desc);

        return $info;
    }

    protected function __construct() {
        
    }

    protected function save_info() {
        $data = [
            'data_version' => $this->data_version,
            'app_version' => $this->app_version,
            'game_version' => $this->game_version,
            'ship_cards' => $this->ship_cards,
            'equip_cards' => $this->equip_cards,
            'skill_cards' => $this->skill_cards,
            'tactics_cards' => $this->tactics_cards,
            'buff_cards' => $this->buff_cards,
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
        $this->buff_cards = isset($data['buff_cards']) ? $data['buff_cards'] : [];
    }

    /////////////////////////////////////////

    const EQUIP_ATTR_NAME = [
        'hp' => '耐久',
        'atk' => '火力',
        'def' => '装甲',
        'torpedo' => '鱼雷',
        'antisub' => '对潜',
        'radar' => '索敌',
        'miss' => '回避',
        'luck' => '幸运',
        'hit' => '命中',
        'airDef' => '对空',
        'aircraftAtk' => '轰炸',
            //'correction' => 'correction',
            //'country' => 'country',
            //'author' => 'author',
            //'missleDefModulus' => 'missleDefModulus',
    ];
    const EQUIP_RANGE_NAME = [
        0 => '未知',
        1 => '短',
        2 => '中',
        3 => '长',
        4 => '超长',
    ];

}
