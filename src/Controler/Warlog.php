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

    public static function show_buff($buff) {
        $from = $buff['from'];
        if ($from[0] == 1) {
            $class = 'success';
        }
        else {
            $class = 'danger';
        }

        $str = '<span class="btn btn-' . $class . ' btn-xs" title="' . $buff['desc'] . '">' . $buff['title'] . '</span> 来自 ';

        $str .= self::show_ship($from);

        $str .= ' 作用于 ';

        foreach ($buff['to'] as $v) {
            $str .= ' ' . self::show_ship($v);
        }

        return $str;
    }

    public static function show_open_air_attack($attack) {
        $str = self::show_ship($attack['from']) . ' 目标<br />';

        foreach ($attack['attack'] as $v) {
            $str .= '<div class="btn-group btn-group-xs">' . self::show_ship($v['target'], false);

            if ($v['plane_type'] == 5) {
                $str .= '<span class="btn btn-info btn-xs" style="color:red;">-';
            }
            else {
                if ($v['critical'] == 1) {
                    $str .= '<span class="btn btn-info btn-xs" style="color:red;font-weight:bold;">伤害：' . $v['damage'] . ($v['ignore'] > 0 ? '(-' . $v['ignore'] . ')' : '') . '击穿';
                }
                else {
                    $str .= '<span class="btn btn-info btn-xs" style="color:red;">伤害：' . $v['damage'];
                }
            }

            $str .= '</span><span class="btn btn-primary btn-xs">击坠：' . $v['drop'] . '/' . $v['amount'] . '</span></div> ';
        }
        return $str;
    }

    public static function show_support_attack($attack) {
        //$str = self::show_ship($attack['from']);

        $str = '';

        foreach ($attack['attack'] as $v) {
            $str .= '<div class="btn-group btn-group-xs">' . self::show_ship($v['target'], false);

            if ($v['critical'] == 1) {
                $str .= '<span class="btn btn-info btn-xs" style="color:red;font-weight:bold;">伤害：' . $v['damage'] . ($v['ignore'] > 0 ? '(-' . $v['ignore'] . ')' : '') . '击穿';
            }
            else {
                $str .= '<span class="btn btn-info btn-xs" style="color:red;">伤害：' . $v['damage'];
            }

            $str .= '</span></div> ';
        }
        return $str;
    }

    public static function show_attack($attack) {
        $str = self::show_ship($attack['from']);

        if (!empty($attack['skill'])) {
            $str .= ' 发动技能 <span class="btn btn-primary btn-xs" title="' . $attack['skill']['desc'] . '">' . $attack['skill']['title'] . ' Lv' . $attack['skill']['level'] . '</span>';
        }

        $str .= ' 目标 ';

        foreach ($attack['attack'] as $v) {
            $str .= '<div class="btn-group btn-group-xs">' . self::show_ship($v['target'], false);

            if ($v['critical'] == 1) {
                $str .= '<span class="btn btn-info btn-xs" style="color:red;font-weight:bold;">伤害：' . $v['damage'] . ($v['ignore'] > 0 ? '(-' . $v['ignore'] . ')' : '') . '击穿';
            }
            else {
                $str .= '<span class="btn btn-info btn-xs" style="color:red;">伤害：' . $v['damage'];
            }

            $str .= '</span></div> ';
        }
        return $str;
    }

    public static function show_ship($ship, $group = true) {
        if ($ship[0] == 1) {
            $class = 'success';
        }
        else {
            $class = 'danger';
        }

        $str = '<span class="btn btn-primary btn-xs">' . $ship[1] . '</span><span class="btn btn-' . $class . '" btn-xs>' . $ship[2] . '</span>';

        if ($group) {
            return '<div class="btn-group btn-group-xs">' . $str . '</div>';
        }
        else {
            return $str;
        }
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

        $mbx = [
            '首页' => '/',
        ];

        $this->display_tpl('warlog/index', [
            'mbx' => $mbx,
            'dir_list' => $dir_list,
            'file_list' => $file_list,
            'cur_file' => $cur_file,
            'log' => $log,
            'path' => $path,
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

        $this->router->send($ret);
    }

    //////////////////////////
    const ATTACK_NAMES = [
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
