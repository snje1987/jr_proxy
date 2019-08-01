<?php

namespace App\Model;

use Exception;

class WarCounter {

    protected $self_ships;
    protected $enemy_ships;

    public function __construct() {
        
    }

    public function set_self_ships($ships) {
        $this->self_ships = $ships;
    }

    public function set_enemy_ships($ships) {
        $this->enemy_ships = $ships;
    }

    public function do_attack($target, $damage) {
        if ($damage == 0) {
            return false;
        }

        if ($target[0] == 1) {
            $list = &$this->self_ships;
        }
        else {
            $list = &$this->enemy_ships;
        }

        $ship_info = $list[$target[1]];

        if ($ship_info['hp_left'] <= 0) {
            return false;
        }

        $ship_info['hp_left'] -= $damage;
        if ($ship_info['hp_left'] < 0) {
            $ship_info['hp_left'] = 0;
        }

        $list[$target[1]] = $ship_info;

        return true;
    }

    public function get_ship($info) {
        if ($info[0] == 1) {
            if (isset($this->self_ships[$info[1]])) {
                return $this->self_ships[$info[1]];
            }
            return null;
        }
        else {
            if (isset($this->enemy_ships[$info[1]])) {
                return $this->enemy_ships[$info[1]];
            }
            return null;
        }
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
            if ($ship_info['hp_left'] * 2 >= $ship_info['hp_max']) {
                $btn = 'info';
                $color = 'black';
            }
            elseif ($ship_info['hp_left'] * 4 >= $ship_info['hp_max']) {
                $btn = 'warning';
                $color = 'black';
            }
            else {
                $btn = 'warning';
                $color = 'red';
            }

            $str .= '<span class="btn btn-' . $btn . '" style="color:' . $color . ';min-width:60px;text-align:right;">' . $ship_info['hp_left'] . '/' . $ship_info['hp_max'] . '</span>';
        }
        else {
            $str .= '<span class="btn btn-warning" style="color:red;min-width:60px;text-align:center;">击沉</span>';
        }

        return '<div class="btn-group btn-group-xs">' . $str . '</div>';
    }

}
