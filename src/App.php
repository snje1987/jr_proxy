<?php

namespace App;

use Workerman\Worker;

class App {

    public static function init() {

        if (defined('APP_ROOT')) {
            return;
        }

        define('APP_ROOT', str_replace('\\', '/', dirname(__DIR__)));

        Config::load();

        define('APP_TPL_DIR', APP_ROOT . '/theme/tpl');
        define('APP_RES_DIR', APP_ROOT . '/theme/res');

        $tmp_dir = APP_ROOT . '/tmp';

        if (!file_exists($tmp_dir)) {
            mkdir($tmp_dir, 0777, true);
        }

        define('APP_TMP_DIR', $tmp_dir);

        $data_dir = APP_ROOT . '/data';

        if (!file_exists($data_dir)) {
            mkdir($data_dir, 0777, true);
        }

        define('APP_DATA_DIR', $data_dir);

        Worker::$logFile = APP_TMP_DIR . '/workerman.log';
    }

    const SHIP_TYPE_CV = 1;
    const SHIP_TYPE_CVL = 2;
    const SHIP_TYPE_AV = 3;
    const SHIP_TYPE_BB = 4;
    const SHIP_TYPE_BBV = 5;
    const SHIP_TYPE_BC = 6;
    const SHIP_TYPE_CA = 7;
    const SHIP_TYPE_CAV = 8;
    const SHIP_TYPE_CLT = 9;
    const SHIP_TYPE_CL = 10;
    const SHIP_TYPE_BM = 11;
    const SHIP_TYPE_DD = 12;
    const SHIP_TYPE_SSV = 13;
    const SHIP_TYPE_SS = 14;
    const SHIP_TYPE_SC = 15;
    const SHIP_TYPE_AP = 16;
    const SHIP_TYPE_FORTRESS = 17;
    const SHIP_TYPE_AIRPORT = 18;
    const SHIP_TYPE_PORT = 19;
    const SHIP_TYPE_MH = 20;
    const SHIP_TYPE_LS = 21;
    const SHIP_TYPE_PS = 22;
    const SHIP_TYPE_ASDG = 23;
    const SHIP_TYPE_AADG = 24;
    const SHIP_TYPE_FLAGSHIP = 99;
    ///////////////////////
    const SHIP_TYPE_HASH = [
        self::SHIP_TYPE_CV => ['title' => '航母', 'group' => 'big',],
        self::SHIP_TYPE_CVL => ['title' => '轻母', 'group' => 'big',],
        self::SHIP_TYPE_AV => ['title' => '装母', 'group' => 'big',],
        self::SHIP_TYPE_BB => ['title' => '战列', 'group' => 'big',],
        self::SHIP_TYPE_BBV => ['title' => '航战', 'group' => 'big',],
        self::SHIP_TYPE_BC => ['title' => '战巡', 'group' => 'big',],
        self::SHIP_TYPE_CA => ['title' => '重巡', 'group' => 'small',],
        self::SHIP_TYPE_CAV => ['title' => '航巡', 'group' => 'small',],
        self::SHIP_TYPE_CLT => ['title' => '雷巡', 'group' => 'small',],
        self::SHIP_TYPE_CL => ['title' => '轻巡', 'group' => 'small',],
        self::SHIP_TYPE_BM => ['title' => '重炮', 'group' => 'small',],
        self::SHIP_TYPE_DD => ['title' => '驱逐', 'group' => 'small',],
        self::SHIP_TYPE_SSV => ['title' => '潜母', 'group' => 'sub',],
        self::SHIP_TYPE_SS => ['title' => '潜艇', 'group' => 'sub',],
        self::SHIP_TYPE_SC => ['title' => '炮潜', 'group' => 'sub',],
        self::SHIP_TYPE_AP => ['title' => '补给', 'group' => 'small',],
        self::SHIP_TYPE_FORTRESS => ['title' => '要塞', 'group' => 'big',],
        self::SHIP_TYPE_AIRPORT => ['title' => '机场', 'group' => 'big',],
        self::SHIP_TYPE_PORT => ['title' => '港口', 'group' => 'big',],
        self::SHIP_TYPE_MH => ['title' => '商船', 'group' => 'big',],
        self::SHIP_TYPE_LS => ['title' => '登陆', 'group' => 'big',],
        self::SHIP_TYPE_PS => ['title' => '海盗', 'group' => 'big',],
        self::SHIP_TYPE_ASDG => ['title' => '导驱', 'group' => 'big',],
        self::SHIP_TYPE_AADG => ['title' => '防驱', 'group' => 'big',],
        26 => ['title' => '导战', 'group' => 'big',],
        98 => ['title' => '未知',],
        self::SHIP_TYPE_FLAGSHIP => ['title' => '旗舰', 'group' => 'big',],
        100 => ['title' => '其它',],
    ];
    /////////////////////////
    const BATTLE_PROP_ATK = 'atk';
    const BATTLE_PROP_DEF = 'def';
    const BATTLE_PROP_TORPEDO = 'torpedo';
    const BATTLE_PROP_ANTISUB = 'antisub';
    const BATTLE_PROP_RADAR = 'radar';
    const BATTLE_PROP_MISS = 'miss';
    const BATTLE_PROP_AIRDEF = 'airDef';
    const BATTLE_PROP_SPEED = 'speed';
    const BATTLE_PROP_RANGE = 'range';
    const BATTLE_PROP_LUCK = 'luck';
    const BATTLE_PROP_HIT = 'hit';
    ///////////////////////////
    const SHIP_BATTLE_PROP_NAME = [
        self::BATTLE_PROP_ATK => '火力',
        self::BATTLE_PROP_DEF => '装甲',
        self::BATTLE_PROP_TORPEDO => '鱼雷',
        self::BATTLE_PROP_ANTISUB => '对潜',
        self::BATTLE_PROP_RADAR => '索敌',
        self::BATTLE_PROP_MISS => '回避',
        self::BATTLE_PROP_AIRDEF => '对空',
        self::BATTLE_PROP_SPEED => '航速',
        self::BATTLE_PROP_RANGE => '射程',
        self::BATTLE_PROP_LUCK => '幸运',
        self::BATTLE_PROP_HIT => '命中',
    ];
    ////////////////////////////
    const SHIP_RES_NAME = [
        'hp' => '耐久',
        'ammo' => '弹药',
        'oil' => '燃料',
    ];
    const EQUIP_PROP_NAME = [
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
        'airDefCorrect' => '对空补正',
        'airDefRate' => '对空倍率',
        'missileHit' => '突防',
        'aluminiumUse' => '铝耗',
        'range' => '射程',
    ];
    const RANGE_NAME = [
        0 => '未知',
        1 => '短',
        2 => '中',
        3 => '长',
        4 => '超长',
    ];
    const COUNTRY_NAME = [
        1 => 'J国',
        2 => 'G国',
        3 => 'E国',
        4 => 'U国',
        5 => 'I国',
        6 => 'F国',
        7 => 'S国',
        8 => 'C国',
        9 => '其他',
        10 => '其他',
        11 => 'Tu国',
        12 => 'Ho国',
        13 => 'Sv国',
        14 => 'Th国',
        15 => 'Au国',
        16 => 'Ca国',
        17 => 'Mo国',
        18 => 'Lc国',
        19 => 'Ch国',
        20 => 'Fi国',
        21 => 'Pi国',
        22 => 'Ar国',
        23 => 'Gr国',
        24 => 'Sp国',
        25 => 'Ys国',
        26 => '',
        27 => '',
    ];

}
