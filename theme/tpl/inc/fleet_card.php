<?php

use App\App;

$fleet_props = [
    'speed_flag_ship' => '旗舰航速',
    'fleet_speed_str' => '舰队航速',
    'speed_max' => '最高航速',
    'speed_min' => '最低航速',
    'speed_avg_str' => '平均航速',
    'speed_avg_str_sub' => '水下平均航速',
    'speed_avg_str_small' => '护卫舰平均航速',
    'speed_avg_str_big' => '主力舰平均航速',
];
$fleet_card = $fleet->get_fleet_card();
?>
<div class="row">
    <div class="panel panel-primary" style="width:100%;margin-bottom:10px;">
        <div class="panel-heading" style="padding:5px;">舰队属性</div>
        <div class="panel-body" style="padding:5px;">
            <?php foreach ($fleet_props as $k => $name) { ?>
                <?php
                if (!isset($fleet_card[$k])) {
                    continue;
                }
                ?>
                <div class="btn btn-group btn-group-xs" style="padding:1px;">
                    <span class="btn btn-primary"><?= $name ?></span>
                    <span class="btn btn-info" style="color:black;min-width:60px;text-align:right;"><?= $fleet_card[$k] ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<div class="row">
    <?php
    $ships = $fleet->get_ships();
    foreach ($ships as $k => $ship) {
        ?>
        <?php if ($k % 3 == 0 && $k != 0) { ?>
        </div><div class="row">
        <?php } ?>
        <div class="col-sm-4" style="padding:2px;">
            <?php include APP_TPL_DIR . '/inc/ship_card.php' ?>
        </div>
    <?php } ?>
</div>