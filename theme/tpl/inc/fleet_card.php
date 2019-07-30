<?php

use App\App;

$fleet_props = [
    'speed_flag_ship' => '旗舰航速',
    'speed_max' => '最高航速',
    'speed_min' => '最低航速',
    'speed_avg_str' => '平均航速',
];
?>
<div class="panel panel-primary" style="width:100%;margin-bottom:10px;">
    <div class="panel-heading" style="padding:5px;">舰队属性</div>
    <div class="panel-body" style="padding:5px;">
        <?php foreach ($fleet_props as $k => $name) { ?>
            <div class="btn btn-group btn-group-xs" style="padding:1px;">
                <span class="btn btn-primary"><?= $name ?></span>
                <span class="btn btn-info" style="color:black;min-width:60px;text-align:right;"><?= $fleet_card[$k] ?></span>
            </div>
        <?php } ?>
    </div>
</div>