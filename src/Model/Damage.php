<?php

namespace App\Model;

use Exception;

class Damage {

    protected $round_group;
    protected $log;
    protected $attack;
    protected $target;
    protected $damage;
    protected $critical;
    protected $extra_def;
    protected $helper;
    protected $true_target;

    public function __construct($round_group, $attack) {
        $this->round_group = $round_group;
        $this->log = $this->round_group->log;
        $this->attack = $attack;
    }

    public function init($info, $self_ship, $enemy_ship) {
        $this->damage = $info['amount'];

        $this->extra_def = 0;
        if ($info['extraDef'] != 0) {
            $this->damage = $this->damage - $info['extraDef'];
            $this->extra_def = $info['extraDef'];
        }

        $this->helper = [];
        if ($info['extraDefHelper'] >= 0 && $info['defType'] == 0) {
            $helper_index = $info['extraDefHelper'];
            $this->helper = [$enemy_ship, $helper_index];
        }

        $this->critical = $info['isCritical'];
        $this->target = [$enemy_ship, $info['index']];
    }

    public function display($defencer) {

        $this->true_target = $this->target;
        if (!empty($this->helper)) {
            $this->true_target = $this->helper;
        }
        elseif (!empty($defencer)) {
            $this->true_target = $defencer;
        }

        $damage_calc = $this->attack->build_calculator();
        if ($damage_calc !== null) {
            $to = $this->log->get_ship($this->true_target);
            $hp_info = $this->round_group->get_ship($this->true_target);

            $to->set_hp($hp_info['hp_left']);
            $damage_calc->to = $to;

            $damage_calc->critical = $this->critical;
        }

        $str = $this->show_damage($damage_calc);
        $str .= ' 目标 ';
        $str .= $this->round_group->show_ship($this->target);

        if (!empty($this->helper)) {
            $str .= ' 被代替 ' . $this->round_group->show_ship($this->helper);
        }
        elseif (!empty($defencer)) {
            $str .= ' 被拦截 ' . $this->round_group->show_ship($defencer);
        }

        $change = $this->round_group->do_attack($this->true_target, $this->damage);
        if ($change) {
            $str .= ' <span class="glyphicon glyphicon-arrow-right"></span> ' . $this->round_group->show_ship($this->true_target);
        }

        return $str;
    }

    ////////////////////////////

    protected function show_damage($damage_calc) {
        if ($this->critical == 1) {
            $flag = '击穿';
            $style = 'color:red;font-weight:bold;';
        }
        else {
            $flag = '伤害';
            $style = 'color:black;';
        }

        $range = '';
        if ($damage_calc !== null) {
            list($min, $max) = $damage_calc->calc_range();

            $range = 'D(' . $min . ', ' . $max . ')  = ';

            if ($this->check_damage($min, $max)) {
                $range = '<span style="color:black;font-weight:normal;">' . $range . '</span>';
            }
            else {
                $range = '<span style="color:#990000;font-weight:bold;">' . $range . '</span>';
            }
        }

        if ($this->extra_def != 0) {
            $extra_str = '(-' . $this->extra_def . ')';
        }
        else {
            $extra_str = '';
        }

        $str = '<div class="btn btn-primary">' . $flag . '</div><div class="btn btn-info" style="' . $style . 'min-width:50px;text-align:right;">' . $range . $this->damage . $extra_str . '</div>';

        return '<div class="btn-group btn-group-xs">' . $str . '</div>';
    }

    public function __get($name) {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }

    protected function check_damage($min, $max) {
        if (($this->damage >= $min && $this->damage <= $max) || $this->damage == 0) {
            return true;
        }
        if ($this->damage > $max) {
            return false;
        }
        if ($this->round_group->check_hp_protect($this->true_target, $this->damage)) {
            return true;
        }
        return false;
    }

}
