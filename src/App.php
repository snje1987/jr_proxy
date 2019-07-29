<?php

namespace App;

use Workerman\Worker;

class App {

    const APP_VERSION = '1.0.2';

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

    const SHIP_TYPE_HASH = [
        1 => '航母',
        2 => '轻母',
        3 => '装母',
        4 => '战列',
        5 => '航战',
        6 => '战巡',
        7 => '重巡',
        8 => '航巡',
        9 => '雷巡',
        10 => '轻巡',
        11 => '重炮',
        12 => '驱逐',
        13 => '潜母',
        14 => '潜艇',
        15 => '炮潜',
        16 => '补给',
        17 => '要塞',
        18 => '机场',
        19 => '港口',
        20 => '商船',
        21 => '登陆',
        22 => '海盗',
        23 => '导驱',
        24 => '防驱',
        26 => '导战',
        98 => '未知',
        99 => '旗舰',
        100 => '其它',
    ];
    const SHIP_BATTLE_PROP_NAME = [
        'hp' => '耐久',
        'hpMax' => '最大耐久',
        'atk' => '火力',
        'def' => '装甲',
        'torpedo' => '鱼雷',
        'antisub' => '对潜',
        'radar' => '索敌',
        'miss' => '回避',
        'airDef' => '对空',
        'speed' => '航速',
        'range' => '射程',
        'luck' => '幸运',
        'hit' => '命中',
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
