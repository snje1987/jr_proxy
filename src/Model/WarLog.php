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
    public $locked_ships = [];
    public $round_groups = [];
    ///////////////////
    protected $war_counter;
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

            $str .= $this->war_counter->show_ship($from);

            $str .= ' 作用于 <br />';

            foreach ($buff['to'] as $v) {
                $str .= ' ' . $this->war_counter->show_ship($v);
            }
            $str .= '<br /><br />';

            $htmls[] = '<div style="margin-bottom:5px;">' . $str . '</div>';
        }

        return implode('', $htmls);
    }

    public function show_locked_ships() {
        $str = '';
        foreach ($this->locked_ships as $ship) {
            $str .= $this->war_counter->show_ship($ship) . ' ';
        }
        return $str;
    }

    public function get_ship($ship_info) {
        if ($ship_info[0] == 1) {
            return $this->self_fleet->get_ship($ship_info[1]);
        }
        else {
            return $this->enemy_fleet->get_ship($ship_info[1]);
        }
    }

    public function fill_fleet_info($damage_calc, $side) {
        if ($side == 1) {
            $damage_calc->formation = $this->self_fleet->formation;
            $damage_calc->war_type = $this->war_type['id'];
            $damage_calc->air_control = $this->air_control;
        }
        else {
            $damage_calc->formation = $this->enemy_fleet->formation;
            if ($this->war_type['id'] == 1 || $this->war_type['id'] == 2) {
                $damage_calc->war_type = $this->war_type['id'];
            }
            else {
                $damage_calc->war_type = 7 - $this->war_type['id'];
            }
            $damage_calc->air_control = 6 - $this->air_control['id'];
        }
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
        $this->war_counter = new WarCounter($this->raw_data['type']);
        $this->war_counter->set_self_ships($this->self_ships);
        $this->war_counter->set_enemy_ships($this->enemy_ships);


        $this->self_fleet->apply_skill($this->enemy_fleet);
        $this->enemy_fleet->apply_skill($this->self_fleet);

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
            'id' => $war_type,
            'title' => $buff['title'],
            'desc' => $buff['desc'],
        ];

        if ($report['airControlType'] > 0) {
            $buff = $this->game_info->get_buff_card('91' . $report['airControlType']);
            $this->air_control = [
                'id' => $report['airControlType'],
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
            if (empty($report[$v])) {
                continue;
            }
            $round_group = new RoundGroup($k, $this, $this->war_counter);
            $round_group->init($report[$v]);

            $this->round_groups[$k] = $round_group;
        }

        if (isset($this->raw_data['war_result']) &&
                isset($this->raw_data['war_result']['extraProgress']) &&
                isset($this->raw_data['war_result']['extraProgress']['nightAttacks'])) {

            $round_group = new RoundGroup('night_attack', $this, $this->war_counter);
            $round_group->init($this->raw_data['war_result']['extraProgress']['nightAttacks'], $this->war_counter);

            $this->round_groups['night_attack'] = $round_group;
        }
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

        $this->self_fleet = new Fleet(0, $title);
        $this->self_fleet->formation = $report['selfFleet']['formation'];
        $this->self_fleet->formation_str = self::FORMATION_NAME[$this->self_fleet->formation];

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

        $this->enemy_fleet = new Fleet(0, $title);
        $this->enemy_fleet->formation = $report['enemyFleet']['formation'];
        $this->enemy_fleet->formation_str = self::FORMATION_NAME[$this->enemy_fleet->formation];

        $ship_list = [];
        $this->enemy_ships = [];

        foreach ($report['enemyShips'] as $info) {

            $ship = new Ship();
            $ship->init_from_warlog($info);

            $short_info = [
                'hp_left' => $ship->res['hp'],
                'hp_max' => $ship->res['hp_max'],
            ];

            if ($this->cfg_show_card_name) {
                $short_info['title'] = $ship->ori_title;
            }
            if (empty($short_info['title'])) {
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
