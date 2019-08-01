<?php

namespace App\Model;

use Exception;

abstract class Tactic {

    public static function get_tactic($tactic_info) {
        $tactic_id = $tactic_info['tid'];
        $level = $tactic_info['level'];

        $class = __NAMESPACE__ . '\\Tactic\\Tactic_' . $tactic_id;
        if (class_exists($class)) {
            return new $class($level);
        }
        return null;
    }

    protected $level;

    public function __construct($level) {
        $this->level = $level;
    }

    /**
     * 根据战术更新船只的战斗属性
     */
    abstract public function apply($from_index, $self_fleet, $enemy_fleet = null);
}
