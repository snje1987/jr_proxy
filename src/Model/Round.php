<?php

namespace App\Model;

use Exception;

class Round {

    protected $round_group;
    protected $log;

    /**
     * @var Attack[]
     */
    protected $attack_list = [];

    public function __construct($round_group) {
        $this->round_group = $round_group;
        $this->log = $this->round_group->log;
    }

    public function append_attack($attack) {
        if (!empty($this->attack_list)) {
            $last = $this->attack_list[count($this->attack_list) - 1];
            if (!$last->is_from_same($attack)) {
                return false;
            }
        }
        $this->attack_list[] = $attack;
        return true;
    }

    public function is_empty() {
        return empty($this->attack_list);
    }

    public function get_round_info() {
        $first = $this->attack_list[0];

        $from = $first->from;
        $skill = $first->skill;

        return [
            'from' => $from,
            'skill' => $skill,
        ];
    }

    public function __get($name) {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }

    public function display() {
        $str = '';

        $group_name = $this->round_group->group_name;

        if ($group_name != 'support_attack') {
            $info = $this->get_round_info();
            $str .= $this->round_group->show_ship($info['from']);

            if (!empty($info['skill'])) {
                $str .= ' 发动技能 ' . $this->show_skill($info['skill']);
            }
            $str .= ' 进行攻击<br />';

            foreach ($this->attack_list as $attack) {
                $str .= $attack->display();
            }
        }

        return $str . '<br />';
    }

    ////////////////////////////////

    protected function show_skill($skill) {
        return '<span class="btn btn-primary btn-xs" title="' . $skill['sid'] . "\n" . $skill['desc'] . '">' . $skill['title'] . ' Lv' . $skill['level'] . '</span>';
    }

}
