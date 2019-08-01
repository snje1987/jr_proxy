<?php

namespace App\Model\Tactic;

use Exception;
use App\Model\Tactic;

/**
 * 炮击训练
 * 当自身在队伍中时，提高舰队中战列巡洋舰的火力值3/5/7/10点（不与相同战术叠加生效）
 */
class Tactic_10000274 extends Tactic {

    public function apply($from_index, $self_fleet, $enemy_fleet = null) {
        
    }

}
