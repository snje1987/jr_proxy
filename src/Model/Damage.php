<?php

namespace App\Model;

use Exception;

class Damage {

    protected $round_group;
    protected $log;
    protected $target;
    protected $damage;
    protected $critical;
    protected $extra_def;
    protected $helper;

    public function __construct($round_group) {
        $this->round_group = $round_group;
        $this->log = $this->round_group->log;
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

    public function display($defencer, $damage_calc) {

        $true_target = $this->target;
        if (!empty($this->helper)) {
            $true_target = $this->helper;
        }
        elseif (!empty($defencer)) {
            $true_target = $defencer;
        }

        if ($damage_calc !== null) {
            $to = $this->log->get_ship($true_target);
            $hp_info = $this->round_group->get_ship($true_target);

            $to = clone $to;
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

        $change = $this->round_group->do_attack($true_target, $this->damage);
        if ($change) {
            $str .= ' <span class="glyphicon glyphicon-arrow-right"></span> ' . $this->round_group->show_ship($true_target);
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
            $range = 'D(' . $min . ', ' . $max . ') = ';
        }

        if ($this->extra_def != 0) {
            $extra_str = '(-' . $this->extra_def . ')';
        }
        else {
            $extra_str = '';
        }

        $str = '<span class="btn btn-primary">' . $flag . '</span><span class="btn btn-info" style="' . $style . 'min-width:50px;text-align:right;">' . $range . $this->damage . $extra_str . '</span>';

        return '<div class="btn-group btn-group-xs">' . $str . '</div>';
    }

    public function __get($name) {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }

}
