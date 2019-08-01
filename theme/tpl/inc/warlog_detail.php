<?php

use App\Controler\Warlog ?>
<div class="panel panel-primary">
    <div class="panel-heading" data-toggle="collapse" data-target="#self_fleet">我方舰队-<?= $log->self_fleet->formation_str ?>-<?= $log->self_fleet->title ?></div>
    <div class="collapse" id="self_fleet">
        <div class="container-fluid" style="padding:5px 20px;">
            <?php $fleet = $log->self_fleet; ?>
            <?php include APP_TPL_DIR . '/inc/fleet_card.php' ?>
        </div>
    </div>
</div>
<div class="panel panel-primary">
    <div class="panel-heading" data-toggle="collapse" data-target="#enemy_fleet">敌方舰队-<?= $log->enemy_fleet->formation_str ?>-<?= $log->enemy_fleet->title ?></div>
    <div class="collapse" id="enemy_fleet">
        <div class="container-fluid" style="padding:5px 20px;">
            <?php $fleet = $log->enemy_fleet; ?>
            <?php include APP_TPL_DIR . '/inc/fleet_card.php' ?>
        </div>
    </div>
</div>
<div class="panel panel-primary">
    <div class="panel-heading">BUFF</div>
    <div class="panel-body">
        <div style="margin-bottom:5px;">
            <?php if (!empty($log->explore_buff)) { ?>
                <span class="btn btn-primary" title="<?= $log->explore_buff['desc'] ?>"><?= $log->explore_buff['title'] ?></span>
            <?php } ?>
            <span class="btn btn-primary" title="<?= $log->war_type['desc'] ?>"><?= $log->war_type['title'] ?></span>
        </div>
        <?= $log->show_buffs() ?>
        <?php if (!empty($log->locked_ships)) { ?>
            <div style="margin-bottom:5px;">
                <p>被锁定船只</p>
                <?php foreach ($log->locked_ships as $ship) { ?>
                    <?= $log->show_ship($ship) ?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
<?php if (!empty($log->support_attack)) { ?>
    <div class="panel panel-primary">
        <div class="panel-heading">支援攻击</div>
        <div class="panel-body">
            <?= $log->show_support_attack() ?>
        </div>
    </div>
<?php } ?>
<?php if (!empty($log->open_air_attack)) { ?>
    <div class="panel panel-primary">
        <div class="panel-heading">航空战</div>
        <div class="panel-body">
            <?php if (!empty($log->air_control)) { ?>
                <div style="margin-bottom:5px;">
                    <span class="btn btn-primary" title="<?= $log->air_control['desc'] ?>"><?= $log->air_control['title'] ?></span>
                </div>
            <?php } ?>
            <?= $log->show_open_air_attack() ?>
        </div>
    </div>
<?php } ?>
<?php foreach (Warlog::ATTACK_NAMES as $key => $name) { ?>
    <?php if (!empty($log->{$key})) { ?>
        <div class="panel panel-primary">
            <div class="panel-heading"><?= $name ?></div>
            <div class="panel-body">
                <?= $log->show_attack($key) ?>
            </div>
        </div>
    <?php } ?>
<?php } ?>