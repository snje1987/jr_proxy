<?php

namespace App\Controler;

use Exception;
use App\Model\CurrentWar;
use App\Model;

class Warlog extends BaseControler {

    protected $game_info;

    public static function get_path_info($path) {
        $path = str_replace('..', '', trim($path, '\\\/'));

        $current_file = false;
        $current_dir = false;

        if (!file_exists(CurrentWar::BASE_DIR . $path)) {
            $current_dir = '';
        }
        elseif (is_dir(CurrentWar::BASE_DIR . $path)) {
            $current_dir = $path;
        }
        else {
            $current_dir = dirname($path);
            $current_dir = rtrim($current_dir, '\\\/');
            $current_file = $path;
        }

        if ($current_dir == '.') {
            $current_dir = '';
        }

        return [$current_dir, $current_file];
    }

    public static function get_list($dir) {
        $full_dir_path = CurrentWar::BASE_DIR . $dir;

        $all_list = [];
        if (file_exists($full_dir_path) && is_dir($full_dir_path)) {
            $all_list = scandir($full_dir_path);
        }

        $dir_list = [];
        $file_list = [];

        if ($dir !== '') {
            $dir .= '/';
        }

        foreach ($all_list as $v) {
            if ($v === '.' || $v === '..') {
                continue;
            }
            if (is_dir($full_dir_path . '/' . $v)) {
                $dir_list[$v] = $dir . $v;
            }
            else {
                if (substr($v, -5) !== '.json') {
                    continue;
                }
                $file_list[$v] = $dir . $v;
            }
        }

        return [$dir_list, $file_list];
    }

    public static function get_mbx($dir) {
        $path = [];

        while (true) {
            if ($dir === '' || $dir === '.') {
                $path['根目录'] = '';
                break;
            }
            else {
                $path[basename($dir)] = rtrim($dir, '\\\/');
            }
            $dir = dirname($dir);
        }

        $path = array_reverse($path, true);

        return $path;
    }

    public function __construct($router) {
        parent::__construct($router);
    }

    public function c_index() {
        $path = isset($_GET['p']) ? strval($_GET['p']) : '';

        list($cur_dir, $cur_file) = self::get_path_info($path);
        list($dir_list, $file_list) = self::get_list($cur_dir);
        $path = self::get_mbx($cur_dir);

        $log = new Model\WarLog();

        if (!$log->init($cur_file)) {
            $log = false;
        }

        $this->display_tpl('warlog/index', [
            'dir_list' => $dir_list,
            'file_list' => $file_list,
            'cur_file' => $cur_file,
            'log' => $log,
            'path' => $path
        ]);
    }

    public function c_replay() {
        $path = isset($_GET['p']) ? strval($_GET['p']) : '';

        $path = ltrim(str_replace('..', '', $path), '\\\/');

        $ret = [
            'error' => -1,
            'msg' => '发生错误',
        ];

        try {
            if ($path == '') {
                throw new Exception('必须指定文件');
            }

            $file = CurrentWar::BASE_DIR . $path;

            if (!file_exists($file) || !is_file($file)) {
                throw new Exception('文件不存在');
            }

            $json = file_get_contents($file);
            file_put_contents(Model\WarReplayer::REPLAY_FILE, $json);

            $ret = [
                'error' => 0,
                'msg' => '设置成功',
            ];
        }
        catch (Exception $ex) {
            $ret = [
                'error' => -1,
                'msg' => $ex->getMessage(),
            ];
        }

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    //////////////////////////
    const ATTACK_NAMES = [
        'support_attack' => '支援攻击',
        'open_air_attack' => '航空战',
        'open_missile_attack' => '开幕导弹',
        'open_anti_sub_attack' => '先制反潜',
        'open_torpedo_attack' => '先制鱼雷',
        'normal_attack' => '炮击战',
        'normal_attack2' => '次轮炮击',
        'close_torpedo_attack' => '鱼雷战',
        'close_missile_attack' => '闭幕导弹',
        'night_attack' => '夜战',
    ];
    const SHIP_ATTR_NAME = [
        'atk' => '火力',
        'def' => '装甲',
        'torpedo' => '鱼雷',
        'antisub' => '对潜',
        'radar' => '索敌',
        'miss' => '回避',
        'airDef' => '对空',
        'speed' => '航速',
            //'range' => '射程',
    ];

}
