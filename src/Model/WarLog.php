<?php

namespace App\Model;

use Exception;
use App\Model\CurrentWar;

class WarLog {

    public $self_ships = [];
    public $enemy_ships = [];
    public $self_fleet = '';
    public $enemy_fleet = '';
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

    public function __construct() {
        $this->game_info = GameInfo::get();
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

            $this->decode();

            return true;
        }
        catch (Exception $ex) {
            return false;
        }
    }

    /////////////////////////////////////////////

    protected $raw_data = null;
    protected $game_info;

    protected function decode() {
        if (!isset($this->raw_data['war_day'])) {
            throw new Exception();
        }
        if (!isset($this->raw_data['war_day']['warReport'])) {
            throw new Exception();
        }
        $report = $this->raw_data['war_day']['warReport'];

        //舰队信息
        $this->self_ships = $this->get_ship_info($report['selfShips']);
        $this->self_fleet = [
            'title' => $report['selfFleet']['title'],
            'formation' => self::FORMATION_NAME[$report['selfFleet']['formation']],
        ];

        $this->enemy_ships = $this->get_ship_info($report['enemyShips']);
        $this->enemy_fleet = [
            'title' => $report['enemyFleet']['title'],
            'formation' => self::FORMATION_NAME[$report['enemyFleet']['formation']],
        ];

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
                $this->locked_ships[] = $this->get_self_ship_name($index);
            }
        }

        if (!empty($report['lockedTargetEnemy'])) {
            foreach ($report['lockedTargetEnemy'] as $index) {
                $this->locked_ships[] = $this->get_enemy_ship_name($index);
            }
        }

        foreach (self::ATTACK_NAMES as $k => $v) {
            $this->{$k} = $this->get_attacks($report[$v]);
        }

        if (isset($this->raw_data['war_result']) && isset($this->raw_data['war_result']['extraProgress']) && isset($this->raw_data['war_result']['extraProgress']['nightAttacks'])) {
            $this->night_attack = $this->get_attacks($this->raw_data['war_result']['extraProgress']['nightAttacks']);
        }
    }

    protected function get_attacks($list) {
        $attacks = [];
        $die = [];

        foreach ($list as $v) {
            $this->add_one_attack($v, $attacks, $die);
        }

        if (empty($attacks)) {
            return [];
        }

        return [
            'attacks' => $attacks,
            'die' => $die,
        ];
    }

    protected function add_one_attack($info, &$attacks, &$die) {
        $from = $info['fromIndex'];

        if ($info['attackSide'] == 1) {
            $prefix = '1_';
            $self_ship = 'self_ship';
            $enemy_ship = 'enemy_ship';
        }
        else {
            $prefix = '2_';
            $self_ship = 'enemy_ship';
            $enemy_ship = 'self_ship';
        }

        if (!isset($attacks[$prefix . $from])) {
            $attacks[$prefix . $from] = [
                'from' => $this->{'get_' . $self_ship . '_name'}($from),
                'attack' => [],
            ];

            if ($info['skillId'] != 0) {
                $skill = $this->game_info->get_skill_card($info['skillId']);
                if ($skill !== null) {
                    $attacks[$prefix . $from]['skill'] = $skill;
                }
            }
        }

        foreach ($info['targetIndex'] as $k => $target) {
            $damage = $info['damages'][$k];

            $amount = $damage['amount'];
            $true_damage = $amount;
            if ($damage['extraDef'] > 0) {
                $true_damage = $amount - $damage['extraDef'];
                $amount .= '(-' . $damage['extraDef'] . ')';
            }

            $attack = [
                'damage' => $amount,
                'critical' => $damage['isCritical'],
                'amount' => $info['planeAmount'],
                'drop' => $info['dropAmount'],
                'ignore' => $info['ignoreDamage'],
                'plane_type' => $info['planeType'],
            ];
            $attack['target'] = $this->{'get_' . $enemy_ship . '_name'}($target);

            $attacks[$prefix . $from]['attack'][] = $attack;

            if ($attack['damage'] != 0) {
                $new_die = $this->update_ship_hp($true_damage, $attack['target'], $enemy_ship . 's');
                if ($new_die !== null) {
                    $die[] = $new_die;
                }
            }
        }
    }

    protected function update_ship_hp($damage, $target, $array_name) {
        $index = $target[1];

        if (!isset($this->{$array_name}[$index]['hp_left'])) {
            $this->{$array_name}[$index]['hp_left'] = $this->{$array_name}[$index]['hp'];
        }
        if ($this->{$array_name}[$index]['hp_left'] > 0) {
            $this->{$array_name}[$index]['hp_left'] -= $damage;
            if ($this->{$array_name}[$index]['hp_left'] <= 0) {
                return $target;
            }
        }
        return null;
    }

    protected function get_buff_info($list, $type) {
        $buffs = [];

        if ($type == 1) {
            $self_ship_name = [$this, 'get_self_ship_name'];
            $enemy_ship_name = [$this, 'get_enemy_ship_name'];
        }
        else {
            $self_ship_name = [$this, 'get_enemy_ship_name'];
            $enemy_ship_name = [$this, 'get_self_ship_name'];
        }

        foreach ($list as $v) {
            $cid = $v['buffCid'];
            $buff_card = $this->game_info->get_buff_card($cid);

            $from = call_user_func($self_ship_name, $v['fromIndex']);

            $to = [];
            if ($v['team'] == 1) {
                foreach ($v['targetIndex'] as $index) {
                    $to[] = call_user_func($self_ship_name, $index);
                }
            }
            else {
                foreach ($v['targetIndex'] as $index) {
                    $to[] = call_user_func($enemy_ship_name, $index);
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

    protected function get_self_ship_name($index) {
        if (isset($this->self_ships[$index])) {
            return [1, $index, $this->self_ships[$index]['title']];
        }
        return null;
    }

    protected function get_enemy_ship_name($index) {
        if (isset($this->enemy_ships[$index])) {
            return [2, $index, $this->enemy_ships[$index]['title']];
        }
        return null;
    }

    protected function get_ship_info($list) {
        $ship_info = [];

        foreach ($list as $info) {
            $ship = [];
            $ship['title'] = $info['title'];
            $ship['level'] = $info['level'];

            $ship_card = $this->game_info->get_ship_card($info['shipCid']);
            if ($ship_card !== null) {
                $ship['desc'] = $ship_card['shipIndex'] . '-' . $ship_card['title'];
                if ($ship_card['evoClass'] > 0) {
                    $ship['desc'] .= '-改' . $ship_card['evoClass'];
                }
            }
            else {
                $ship['desc'] = '';
            }

            foreach (self::SHIP_ATTR_NAME as $k => $v) {
                if (isset($info[$k])) {
                    $ship[$k] = $info[$k];
                }
            }

            $ship['range'] = GameInfo::EQUIP_RANGE_NAME[$info['range']];

            $ship['equip'] = [];
            foreach ($info['equipment'] as $cid) {
                if ($cid < 0) {
                    continue;
                }
                $card = $this->game_info->get_equip_card($cid);
                $ship['equip'][] = $card;

                foreach (self::SHIP_ATTR_HASH as $k => $v) {
                    if (!isset($card[$k])) {
                        continue;
                    }
                    if (!is_array($ship[$v])) {
                        $ship[$v] = [$ship[$v], $card[$k]];
                    }
                    else {
                        $ship[$v][1] += $card[$k];
                    }
                }
            }

            $ship['tactics'] = [];
            foreach ($info['tactics'] as $cid) {
                if ($cid == 0) {
                    continue;
                }
                $ship['tactics'][] = $this->game_info->get_tactics_card($cid);
            }

            $ship['skill'] = $this->game_info->get_skill_card($info['skillId']);

            $index = $info['indexInFleet'];
            $ship_info[$index] = $ship;
        }

        ksort($ship_info, SORT_NUMERIC);

        return $ship_info;
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
    const SHIP_ATTR_NAME = [
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
    ];

}
