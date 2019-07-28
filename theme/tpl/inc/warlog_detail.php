<?php

use App\Controler\Warlog ?>
<div class="panel panel-primary">
    <div class="panel-heading">我方舰队-<?= $log->self_fleet['formation'] ?>-<?= $log->self_fleet['title'] ?></div>
    <table class="table table-bordered table-hover table-condensed table-striped">
        <tr>
            <th class="text-center">名称</th>
            <th class="text-center">属性</th>
            <th class="text-center">装备</th>
            <th class="text-center">技能</th>
            <th class="text-center">战术</th>
        </tr>
        <?php foreach ($log->self_ships as $index => $ship) { ?>
            <?php $class = 'success' ?>
            <?php include APP_TPL_DIR . '/inc/warlog_ship.php' ?>
        <?php } ?>
    </table>
</div>
<div class="panel panel-primary">
    <div class="panel-heading">敌方舰队-<?= $log->enemy_fleet['formation'] ?>-<?= $log->enemy_fleet['title'] ?></div>
    <table class="table table-bordered table-hover table-condensed table-striped">
        <tr>
            <th class="text-center">名称</th>
            <th class="text-center">属性</th>
            <th class="text-center">装备</th>
            <th class="text-center">技能</th>
            <th class="text-center">战术</th>
        </tr>
        <?php foreach ($log->enemy_ships as $index => $ship) { ?>
            <?php $class = 'danger' ?>
            <?php include APP_TPL_DIR . '/inc/warlog_ship.php' ?>
        <?php } ?>
    </table>
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
        <?php foreach ($log->buffs as $buff) { ?>
            <div style="margin-bottom:5px;"><?= Warlog::show_buff($buff) ?></div>
        <?php } ?>
        <?php if (!empty($log->locked_ships)) { ?>
            <div style="margin-bottom:5px;">
                <p>被锁定船只</p>
                <?php foreach ($log->locked_ships as $ship) { ?>
                    <?= Warlog::show_ship($ship) ?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
<?php if (!empty($log->support_attack)) { ?>
    <div class="panel panel-primary">
        <div class="panel-heading">支援攻击</div>
        <div class="panel-body">
            <?php
            $info = $log->support_attack;
            foreach ($info['attacks'] as $attack) {
                ?>
                <div style="margin-bottom:5px;"><?= Warlog::show_support_attack($attack) ?></div>
            <?php } ?>
            <?php if (!empty($info['die'])) { ?>
                <p>击沉船只</p>
                <?php foreach ($info['die'] as $ship) { ?>
                    <?= Warlog::show_ship($ship) ?>
                <?php } ?>
            <?php } ?>
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
            <?php
            $info = $log->open_air_attack;
            foreach ($info['attacks'] as $attack) {
                ?>
                <div style="margin-bottom:5px;">
                    <?= Warlog::show_open_air_attack($attack) ?>
                    <br /><br />
                </div>
            <?php } ?>
            <?php if (!empty($info['die'])) { ?>
                <p>击沉船只</p>
                <?php foreach ($info['die'] as $ship) { ?>
                    <?= Warlog::show_ship($ship) ?>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
<?php } ?>
<?php foreach (Warlog::ATTACK_NAMES as $key => $name) { ?>
    <?php if (!empty($log->{$key})) { ?>
        <div class="panel panel-primary">
            <div class="panel-heading"><?= $name ?></div>
            <div class="panel-body">
                <?php
                $info = $log->{$key};
                foreach ($info['attacks'] as $attack) {
                    ?>
                    <div style="margin-bottom:5px;"><?= Warlog::show_attack($attack) ?></div>
                <?php } ?>
                <?php if (!empty($info['die'])) { ?>
                    <p>击沉船只</p>
                    <?php foreach ($info['die'] as $ship) { ?>
                        <?= Warlog::show_ship($ship) ?>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
<?php } ?>