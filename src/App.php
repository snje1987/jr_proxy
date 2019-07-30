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

    const SHIP_TYPE_HASH = [
        1 => [
            'title' => '航母',
            'group' => 'big',
        ],
        2 => [
            'title' => '轻母',
            'group' => 'big',
        ],
        3 => [
            'title' => '装母',
            'group' => 'big',
        ],
        4 => [
            'title' => '战列',
            'group' => 'big',
        ],
        5 => [
            'title' => '航战',
            'group' => 'big',
        ],
        6 => [
            'title' => '战巡',
            'group' => 'big',
        ],
        7 => [
            'title' => '重巡',
            'group' => 'small',
        ],
        8 => [
            'title' => '航巡',
            'group' => 'small',
        ],
        9 => [
            'title' => '雷巡',
            'group' => 'small',
        ],
        10 => [
            'title' => '轻巡',
            'group' => 'small',
        ],
        11 => [
            'title' => '重炮',
            'group' => 'small',
        ],
        12 => [
            'title' => '驱逐',
            'group' => 'small',
        ],
        13 => [
            'title' => '潜母',
            'group' => 'sub',
        ],
        14 => [
            'title' => '潜艇',
            'group' => 'sub',
        ],
        15 => [
            'title' => '炮潜',
            'group' => 'sub',
        ],
        16 => [
            'title' => '补给',
            'group' => 'small',
        ],
        17 => [
            'title' => '要塞',
            'group' => 'big',
        ],
        18 => [
            'title' => '机场',
            'group' => 'big',
        ],
        19 => [
            'title' => '港口',
            'group' => 'big',
        ],
        20 => [
            'title' => '商船',
            'group' => 'big',
        ],
        21 => [
            'title' => '登陆',
            'group' => 'big',
        ],
        22 => [
            'title' => '海盗',
            'group' => 'big',
        ],
        23 => [
            'title' => '导驱',
            'group' => 'big',
        ],
        24 => [
            'title' => '防驱',
            'group' => 'big',
        ],
        26 => [
            'title' => '导战',
            'group' => 'big',
        ],
        98 => [
            'title' => '未知',
        ],
        99 => [
            'title' => '旗舰',
            'group' => 'big',
        ],
        100 => [
            'title' => '其它',
        ],
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
