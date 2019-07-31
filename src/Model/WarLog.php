<?php

namespace App\Model;

use Exception;
use App\Model\CurrentWar;

class WarLog {

    public $self_ships = [];
    public $enemy_ships = [];
    public $self_fleet;
    public $enemy_fleet;
    public $buffs = [];
    public $explore_buff = [];
    public $war_type = [];
    public $air_control = [];
    public $support_attack = [];
    public $open_air_attack = [];
    public $open_missile_attack = [];
    public $open_anti_sub_attack = [];
    public $open_torpedo_attack = [];
    public $normal_attack = [];
    public $normal_attack2 = [];
    public $close_torpedo_attack = [];
    public $close_missile_attack = [];
    public $night_attack = [];
    public $locked_ships = [];
    ///////////////////

    protected $cfg_show_card_name = 1;

    public function __construct() {
        $this->game_info = GameInfo::get();
        $this->cfg_show_card_name = \App\Config::get('main', 'show_card_name', 1);
    }

    public function init($file) {
        try {
            if ($file === false) {
                throw new Exception();
            }

            $json = file_get_contents(CurrentWar::BASE_DIR . $file);
            $this->raw_data = json_decode($json, true);
            if ($this->raw_data === null) {
                throw new Exception();
            }

            $this->raw_data = LogUpgrader::upgrade(CurrentWar::BASE_DIR . $file, $this->raw_data);
            $this->decode();

            return true;
        }
        catch (Exception $ex) {
            return false;
        }
    }

    public function show_support_attack() {
        $htmls = [];
        foreach ($this->support_attack as $attack) {
            $str = '';

            foreach ($attack['attack'] as $damage) {
                $str .= $this->show_damage($damage);
                $str .= ' 目标 ' . $this->attack_ship($damage['target'], $damage['damage']);

                $str .= '<br />';
            }

            $htmls[] = '<div style="margin-bottom:5px;">' . $str . '</div>';
        }

        return implode('', $htmls);
    }

    public function show_open_air_attack() {
        $htmls = [];
        foreach ($this->open_air_attack as $attack) {

            $str = self::show_ship($attack['from']) . ' 发动空袭<br />';

            foreach ($attack['attack'] as $damage) {

                $str .= $this->show_drop($damage) . ' ';
                $str .= $this->show_damage($damage);
                $str .= ' 目标 ' . $this->attack_ship($damage['target'], $damage['damage']);

                $str .= '<br />';
            }

            $str .= '<br />';

            $htmls[] = '<div style="margin-bottom:5px;">' . $str . '</div>';
        }

        return implode('', $htmls);
    }

    public function show_attack($name) {
        $htmls = [];

        if (!isset($this->{$name})) {
            return '';
        }

        foreach ($this->{$name} as $attack) {

            $str = self::show_ship($attack['from']);

            if (!empty($attack['skill'])) {
                $str .= ' 发动技能 ' . $this->show_skill($attack['skill']);
            }

            $str .= ' 进行攻击<br />';

            foreach ($attack['attack'] as $damage) {

                $str .= $this->show_damage($damage);
                $str .= ' 目标 ';

                if (!empty($damage['helper'])) {
                    $str .= $this->show_ship($damage['target']) . ' 被代替 ' . $this->attack_ship($damage['helper'], $damage['damage']);
                }
                elseif (!empty($damage['defencer'])) {
                    $str .= $this->show_ship($damage['target']) . ' 被拦截 ' . $this->attack_ship($damage['defencer'], $damage['damage']);
                }
                else {
                    $str .= $this->attack_ship($damage['target'], $damage['damage']);
                }

                $str .= '<br />';
            }

            $str .= '<br />';

            $htmls[] = '<div style="margin-bottom:5px;">' . $str . '</div>';
        }

        return implode('', $htmls);
    }

    public function show_buffs() {
        $htmls = [];

        foreach ($this->buffs as $buff) {
            $from = $buff['from'];
            if ($from[0] == 1) {
                $class = 'success';
            }
            else {
                $class = 'danger';
            }

            $str = '<span class="btn btn-' . $class . ' btn-xs" title="' . $buff['desc'] . '">' . $buff['title'] . '</span> 来自 ';

            $str .= self::show_ship($from);

            $str .= ' 作用于 <br />';

            foreach ($buff['to'] as $v) {
                $str .= ' ' . self::show_ship($v);
            }
            $str .= '<br /><br />';

            $htmls[] = '<div style="margin-bottom:5px;">' . $str . '</div>';
        }

        return implode('', $htmls);
    }

    public function show_damage($damage) {
        if ($damage['critical'] == 1) {
            $flag = '击穿';
            $style = 'color:red;font-weight:bold;';
        }
        else {
            $flag = '伤害';
            $style = 'color:black;';
        }

        $str = '<span class="btn btn-primary">' . $flag . '</span><span class="btn btn-info" style="' . $style . 'min-width:50px;text-align:right;">' . $damage['damage'] . $damage['extra'] . '</span>';

        return '<div class="btn-group btn-group-xs">' . $str . '</div>';
    }

    public function show_drop($damage) {
        $str = '<span class="btn btn-primary">击坠</span>';

        $str .= '<span class="btn btn-info" style="color:black;">' . $damage['drop'] . '/' . $damage['amount'] . '</span>';

        return '<div class="btn-group btn-group-xs">' . $str . '</div>';
    }

    public function show_ship($ship) {
        if ($ship[0] == 1) {
            $class = 'success';
            $list = $this->self_ships;
        }
        else {
            $class = 'danger';
            $list = $this->enemy_ships;
        }

        $ship_info = $list[$ship[1]];

        $str = '<span class="btn btn-primary">' . $ship[1] . '</span><span class="btn btn-' . $class . '" btn-xs>' . $ship_info['title'] . '</span>';

        if ($ship_info['hp_left'] > 0) {
            $str .= '<span class="btn btn-info" style="color:black;min-width:70px;text-align:right;">' . $ship_info['hp_left'] . '/' . $ship_info['hp_max'] . '</span>';
        }
        else {
            $str .= '<span class="btn btn-warning" style="color:black;min-width:70px;text-align:center;">击沉</span>';
        }

        return '<div class="btn-group btn-group-xs">' . $str . '</div>';
    }

    public function show_skill($skill) {
        return '<span class="btn btn-primary btn-xs" title="' . $skill['desc'] . '">' . $skill['title'] . ' Lv' . $skill['level'] . '</span>';
    }

    public function attack_ship($ship, $damage) {
        $str = $this->show_ship($ship);

        if ($damage == 0) {
            return $str;
        }

        if ($ship[0] == 1) {
            $list = &$this->self_ships;
        }
        else {
            $list = &$this->enemy_ships;
        }

        $ship_info = $list[$ship[1]];

        if ($ship_info['hp_left'] <= 0) {
            return $str;
        }

        $ship_info['hp_left'] -= $damage;
        if ($ship_info['hp_left'] < 0) {
            $ship_info['hp_left'] = 0;
        }

        $list[$ship[1]] = $ship_info;

        $str .= ' <span class="glyphicon glyphicon-arrow-right"></span> ' . $this->show_ship($ship);

        return $str;
    }

    /////////////////////////////////////////////

    protected $raw_data = null;
    protected $game_info;

    protected function decode() {
        if (!isset($this->raw_data['fleet'])) {
            throw new Exception();
        }
        if (!isset($this->raw_data['war_day'])) {
            throw new Exception();
        }
        if (!isset($this->raw_data['war_day']['warReport'])) {
            throw new Exception();
        }
        $report = $this->raw_data['war_day']['warReport'];

        $this->get_self_fleet();
        $this->get_enemy_fleet();

        //buff信息
        $self_buffs = $this->get_buff_info($report['selfBuffs'], 1);
        $enemy_buffs = $this->get_buff_info($report['enemyBuffs'], 2);

        $this->buffs = array_merge($self_buffs, $enemy_buffs);
        if ($report['hasExploreBuff'] == 1) {
            $buff_card = $this->game_info->get_buff_card(901);
            $this->explore_buff = [
                'title' => $buff_card['title'],
                'desc' => $buff_card['desc'],
            ];
        }

        $war_type = $report['warType'];

        //航向信息
        $buff = $this->game_info->get_buff_card('93' . $war_type);
        $this->war_type = [
            'title' => $buff['title'],
            'desc' => $buff['desc'],
        ];

        if ($report['airControlType'] > 0) {
            $buff = $this->game_info->get_buff_card('91' . $report['airControlType']);
            $this->air_control = [
                'title' => $buff['title'],
                'desc' => $buff['desc'],
            ];
        }

        if (!empty($report['lockedTargetSelf'])) {
            foreach ($report['lockedTargetSelf'] as $index) {
                $this->locked_ships[] = [1, $index];
            }
        }

        if (!empty($report['lockedTargetEnemy'])) {
            foreach ($report['lockedTargetEnemy'] as $index) {
                $this->locked_ships[] = [2, $index];
            }
        }

        foreach (self::ATTACK_NAMES as $k => $v) {
            $merge_all = false;
            if ($k == 'open_air_attack' || $k == 'open_torpedo_attack' || $k == 'close_torpedo_attack') {
                $merge_all = true;
            }
            $this->{$k} = $this->get_attacks($report[$v], $merge_all);
        }

        if (isset($this->raw_data['war_result']) && isset($this->raw_data['war_result']['extraProgress']) && isset($this->raw_data['war_result']['extraProgress']['nightAttacks'])) {
            $this->night_attack = $this->get_attacks($this->raw_data['war_result']['extraProgress']['nightAttacks'], false);
        }
    }

    protected function get_attacks($list, $merge_all) {
        $attacks = [];
        foreach ($list as $v) {
            $tmp = $this->add_one_attack($v);
            $last = count($attacks) - 1;
            if ($last >= 0 &&
                    $tmp['from'][0] == $attacks[$last]['from'][0] &&
                    $tmp['from'][1] == $attacks[$last]['from'][1]) {
                foreach ($tmp['attack'] as $v) {
                    $attacks[$last]['attack'][] = $v;
                }
            }
            else {
                $attacks[] = $tmp;
            }
        }

        if ($merge_all) {
            $ori_attacks = $attacks;
            $attacks = [];
            foreach ($ori_attacks as $v) {
                $index = $v['from'][0] . '_' . $v['from'][1];
                if (!isset($attacks[$index])) {
                    $attacks[$index] = $v;
                }
                else {
                    foreach ($v['attack'] as $v) {
                        $attacks[$index]['attack'][] = $v;
                    }
                }
            }
        }

        if (empty($attacks)) {
            return [];
        }

        return $attacks;
    }

    protected function add_one_attack($info) {
        $from = $info['fromIndex'];

        if ($info['attackSide'] == 1) {
            $self_ship = 1;
            $enemy_ship = 2;
        }
        else {
            $self_ship = 2;
            $enemy_ship = 1;
        }

        $attack = [
            'from' => [$self_ship, $from],
            'attack' => [],
        ];

        if ($info['skillId'] != 0) {
            $skill = $this->game_info->get_skill_card($info['skillId']);
            if ($skill !== null) {
                $attack['skill'] = $skill;
            }
        }

        foreach ($info['targetIndex'] as $k => $target) {
            $raw_damage = $info['damages'][$k];

            $amount = $raw_damage['amount'];
            $extra = [];
            if ($raw_damage['extraDef'] != 0) {
                $amount = $amount - $raw_damage['extraDef'];
                $extra[] = '-' . $raw_damage['extraDef'];
            }

            if (!empty($extra)) {
                $extra_str = '(' . implode('', $extra) . ')';
            }
            else {
                $extra_str = '';
            }

            $defencer = [];
            $helper = [];
            if (!empty($info['tmdDef'])) {
                $defencer_index = $info['tmdDef'][0];
                $defencer = [$enemy_ship, $defencer_index];
            }
            elseif ($raw_damage['extraDefHelper'] >= 0 && $raw_damage['defType'] == 0) {
                $helper_index = $raw_damage['extraDefHelper'];
                $helper = [$enemy_ship, $helper_index];
            }

            $damage = [
                'damage' => $amount,
                'extra' => $extra_str,
                'critical' => $raw_damage['isCritical'],
                'amount' => $info['planeAmount'],
                'drop' => $info['dropAmount'],
                'plane_type' => $info['planeType'],
                'defencer' => $defencer,
                'helper' => $helper,
            ];
            $damage['target'] = [$enemy_ship, $target];

            $true_target = $damage['target'];
            if (!empty($damage['helper'])) {
                $true_target = $damage['helper'];
            }

            $attack['attack'][] = $damage;
        }

        return $attack;
    }

    protected function get_buff_info($list, $type) {
        $buffs = [];

        if ($type == 1) {
            $self_ship = 1;
            $enemy_ship = 2;
        }
        else {
            $self_ship = 2;
            $enemy_ship = 1;
        }

        foreach ($list as $v) {
            $cid = $v['buffCid'];
            $buff_card = $this->game_info->get_buff_card($cid);

            $from = [$self_ship, $v['fromIndex']];

            $to = [];
            if ($v['team'] == 1) {
                foreach ($v['targetIndex'] as $index) {
                    $to[] = [$self_ship, $index];
                }
            }
            else {
                foreach ($v['targetIndex'] as $index) {
                    $to[] = [$enemy_ship, $index];
                }
            }

            $title = $buff_card['title'];
            if ($buff_card['level'] > 0) {
                $title .= ' Lv' . $buff_card['level'];
            }

            $buffs[] = [
                'from' => $from,
                'to' => $to,
                'title' => $title,
                'desc' => $buff_card['desc'],
            ];
        }

        return $buffs;
    }

    protected function get_self_fleet() {
        $report = $this->raw_data['war_day']['warReport'];

        $title = $report['selfFleet']['title'];
        $formation = self::FORMATION_NAME[$report['selfFleet']['formation']];

        $this->self_fleet = new Fleet(0, $title);
        $this->self_fleet->formation = $formation;

        $ship_list = [];
        $this->self_ships = [];

        foreach ($this->raw_data['fleet'] as $info) {
            try {
                $ship = new Ship();
                $ship->init_from_save($info);

                $short_info = [
                    'hp_left' => $ship->res['hp'],
                    'hp_max' => $ship->res['hp_max'],
                ];

                if ($this->cfg_show_card_name) {
                    $short_info['title'] = $ship->ori_title;
                }
                else {
                    $short_info['title'] = $ship->title;
                }

                $ship_list[] = $ship;
                $this->self_ships[] = $short_info;
            }
            catch (Exception $ex) {
                
            }
        }

        $this->self_fleet->set_ships($ship_list);
    }

    protected function get_enemy_fleet() {
        $report = $this->raw_data['war_day']['warReport'];

        $title = $report['enemyFleet']['title'];
        $formation = self::FORMATION_NAME[$report['enemyFleet']['formation']];

        $this->enemy_fleet = new Fleet(0, $title);
        $this->enemy_fleet->formation = $formation;

        $ship_list = [];
        $this->enemy_ships = [];

        foreach ($report['enemyShips'] as $info) {

            $ship = new Ship();
            $ship->init_from_warlog($info);

            $short_info = [
                'hp_left' => $ship->res['hp'],
                'hp_max' => $ship->res['hp_max'],
            ];

            if ($this->cfg_show_card_name && !empty($ship->ori_title)) {
                $short_info['title'] = $ship->ori_title;
            }
            else {
                $short_info['title'] = $ship->title;
            }

            $index = $info['indexInFleet'];

            $ship_list[$index] = $ship;
            $this->enemy_ships[$index] = $short_info;
        }

        ksort($ship_list, SORT_NUMERIC);
        ksort($this->enemy_ships, SORT_NUMERIC);

        $this->enemy_fleet->set_ships($ship_list);
    }

    //////////////////////////

    const FORMATION_NAME = [
        1 => '单纵',
        2 => '复纵',
        3 => '轮型',
        4 => '梯形',
        5 => '单橫',
    ];
    const ATTACK_NAMES = [
        'support_attack' => 'supportAttack',
        'open_air_attack' => 'openAirAttack',
        'open_missile_attack' => 'openMissileAttack',
        'open_anti_sub_attack' => 'openAntiSubAttack',
        'open_torpedo_attack' => 'openTorpedoAttack',
        'normal_attack' => 'normalAttacks',
        'normal_attack2' => 'normalAttacks2',
        'close_torpedo_attack' => 'closeTorpedoAttack',
        'close_missile_attack' => 'closeMissileAttack',
    ];
    const SHIP_ATTR_HASH = [
        'atk' => 'atk',
        'def' => 'def',
        'torpedo' => 'torpedo',
        'antisub' => 'antisub',
        'radar' => 'radar',
        'miss' => 'miss',
        'airDef' => 'airDef',
        'speed' => 'speed',
    ];

}
