<?php

namespace App\Model\DamageCalc;

use Exception;
use App\App;

class BaseAttack {

    protected $formation = null; //阵型
    protected $war_type = null; //航向信息
    protected $air_control = null;
    protected $critical = null;
    protected $group_name;

    /**
     * @var \App\Model\Ship 
     */
    protected $from = null;

    /**
     * @var \App\Model\Ship 
     */
    protected $to = null;
    //////////////////////

    protected $ammo_var;
    protected $hp_var;
    protected $formation_var;
    protected $war_type_var;
    protected $critical_var;
    protected $skill_var = [1, 1];
    protected $damage_add = [0, 0];
    protected $damage_var = [1, 1];

    public function __construct($group_name) {
        $this->group_name = $group_name;
    }

    public function __get($name) {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }

    public function __set($name, $value) {
        $this->{$name} = $value;
    }

    public function calc_range() {
        return [0, 0];
    }

    public function calc_common_var() {
        if (isset(static::FORMATION_VAR[$this->formation])) {
            $this->formation_var = static::FORMATION_VAR[$this->formation];
        }
        else {
            $this->formation_var = 1;
        }

        if (isset(static::WAR_TYPE_VAR[$this->war_type])) {
            $this->war_type_var = static::WAR_TYPE_VAR[$this->war_type];
        }
        else {
            $this->war_type_var = 1;
        }

        $res = $this->from->res;

        if ($res['hp'] * 2 >= $res['hp_max']) {
            $this->hp_var = 1;
        }
        elseif ($res['hp'] * 4 >= $res['hp_max']) {
            $this->hp_var = 0.6;
        }
        else {
            $this->hp_var = 0.3;
        }

        $this->ammo_var = ceil($res['ammo'] * 10 / $res['ammo_max']) * 2 / 10;
        if ($this->ammo_var > 1) {
            $this->ammo_var = 1;
        }

        if ($this->critical == 1) {
            $this->critical_var = 1.5;
        }
        else {
            $this->critical_var = 1;
        }
    }

    //////////////////////////

    const FORMATION_VAR = [
    ];
    const WAR_TYPE_VAR = [
    ];

    /*
      1 => '占据制空权',
      2 => '制空优势',
      3 => '制空均势',
      4 => '制空劣势',
      5 => '丧失制空权',
     */
    /*
      1 => '同航战',
      2 => '反航战',
      3 => 'T字有利',
      4 => 'T字不利',
     */
    /*
      1 => '单纵',
      2 => '复纵',
      3 => '轮型',
      4 => '梯形',
      5 => '单橫',
     */
}
