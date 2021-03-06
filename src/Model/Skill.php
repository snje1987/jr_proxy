<?php

namespace App\Model;

use Exception;

abstract class Skill {

    public static function get_skill($skill) {
        $skill_id = $skill['sid'];
        $level = $skill['level'];

        $class = __NAMESPACE__ . '\\Skill\\Skill_' . $skill_id;
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
     * 根据技能更新船只的战斗属性
     * @param int $from_index
     * @param Fleet $self_fleet
     * @param Fleet $enemy_fleet
     */
    public function apply($from_index, $self_fleet, $enemy_fleet = null) {
        
    }

}
