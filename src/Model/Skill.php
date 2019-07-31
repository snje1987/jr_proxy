<?php

namespace App\Model;

use Exception;

abstract class Skill {

    public static function get_skill($skill) {
        $skill_id = $skill['sid'];
        $level = $skill['level'];

        $class = __NAMESPACE__ . '\\ShipSkill\\Skill_' . $skill_id;
        if (class_exists($class)) {
            return new $class($level);
        }
        return null;
    }

    /**
     * 根据技能更新船只的战斗属性
     */
    abstract public function apply($self_fleet, $enemy_fleet = null);
}
