<?php

namespace App\Model;

use Exception;

class RoundGroup {

    protected $group_name;
    protected $log;
    protected $war_counter;
    protected $round_list;

    public function __construct($group_name, $log, $war_counter) {
        $this->group_name = $group_name;
        $this->log = $log;
        $this->war_counter = $war_counter;
    }

    public function init($list) {
        $cur_round = new Round($this);
        foreach ($list as $attack_info) {
            try {
                $attack = new Attack($this);
                $attack->init($attack_info);

                if (!$cur_round->append_attack($attack)) {
                    $this->round_list[] = $cur_round;
                    $cur_round = new Round($this);
                    $cur_round->append_attack($attack);
                }
            }
            catch (Exception $ex) {
                
            }
        }

        if (!$cur_round->is_empty()) {
            $this->round_list[] = $cur_round;
        }
    }

    public function display() {
        $str = '';
        foreach ($this->round_list as $round) {
            $str .= $round->display();
        }
        return $str;
    }

    public function show_ship($info) {
        return $this->war_counter->show_ship($info);
    }

    public function get_ship($info) {
        return $this->war_counter->get_ship($info);
    }

    public function do_attack($target, $damage) {
        return $this->war_counter->do_attack($target, $damage);
    }

    public function check_hp_protect($target, $damage, $min, $max) {
        return $this->war_counter->check_hp_protect($target, $damage, $min, $max);
    }

    public function __get($name) {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }

}
