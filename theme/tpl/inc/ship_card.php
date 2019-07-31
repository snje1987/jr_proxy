<?php

use App\App;

$basic_props = [
    'shipIndex' => '编号',
    'evoClass' => '改造',
    'country' => '国家',
    'level' => '等级',
    'love' => '好感',
    'isLocked' => '锁定',
    'married' => '戒指',
];

$ship_info = $ship->get_ship_card();
?>
<div class="panel panel-primary">
    <div class="panel-heading" style="padding:5px;"><?= $ship_info['title'] ?></div>

    <div class="panel-body" style="padding:5px;">
        <div class="btn btn-group btn-group-xs" style="padding:1px;">
            <span class="btn btn-primary">类型</span>
            <span class="btn btn-info" style="color:black;min-width:60px;text-align:right;"><?= $ship_info['type'] ?></span>
        </div>
        <?php if (isset($ship_info['ori_title'])) { ?>
            <div class="btn btn-group btn-group-xs" style="padding:1px;">
                <span class="btn btn-primary">图鉴名称</span>
                <span class="btn btn-info" style="color:black;min-width:60px;text-align:right;"><?= $ship_info['ori_title'] ?></span>
            </div>
        <?php } ?>
    </div>

    <div style="width:100%;text-align:left;background-color:#f5f5f5;color:#337ab7;padding:5px;border:1px solid #ddd;border-left: 0;border-right:0;">基本属性</div>

    <div class="panel-body" style="padding:5px;">
        <?php foreach ($basic_props as $k => $name) { ?>
            <?php
            if (!isset($ship_info[$k])) {
                continue;
            }
            ?>
            <div class="btn btn-group btn-group-xs" style="padding:1px;">
                <span class="btn btn-primary"><?= $name ?></span>
                <span class="btn btn-info" style="color:black;min-width:60px;text-align:right;">
                    <?= is_array($ship_info[$k]) ? $ship_info[$k][0] . '+' . $ship_info[$k][1] . '=' . ($ship_info[$k][0] + $ship_info[$k][1]) : $ship_info[$k] ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div style="width:100%;text-align:left;background-color:#f5f5f5;color:#337ab7;padding:5px;border:1px solid #ddd;border-left: 0;border-right:0;">战斗属性</div>

    <div class="panel-body" style="padding:5px;">
        <?php foreach (App::SHIP_RES_NAME as $k => $name) { ?>
            <?php
            if (!isset($ship_info[$k])) {
                continue;
            }
            ?>
            <div class="btn btn-group btn-group-xs" style="padding:1px;">
                <span class="btn btn-primary"><?= $name ?></span>
                <span class="btn btn-info" style="color:black;min-width:90px;text-align:right;">
                    <?= $ship_info[$k] ?>/<?= is_array($ship_info[$k . '_max']) ? $ship_info[$k . '_max'][0] . '+' . $ship_info[$k . '_max'][1] . '=' . ($ship_info[$k . '_max'][0] + $ship_info[$k . '_max'][1]) : $ship_info[$k . '_max'] ?>
                </span>
            </div>
        <?php } ?>
        <?php foreach (App::SHIP_BATTLE_PROP_NAME as $k => $name) { ?>
            <?php
            if (!isset($ship_info[$k])) {
                continue;
            }
            ?>
            <div class="btn btn-group btn-group-xs" style="padding:1px;">
                <span class="btn btn-primary"><?= $name ?></span>
                <span class="btn btn-info" style="color:black;min-width:90px;text-align:right;">
                    <?= is_array($ship_info[$k]) ? $ship_info[$k][0] . '+' . $ship_info[$k][1] . '=' . ($ship_info[$k][0] + $ship_info[$k][1]) : $ship_info[$k] ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div style="width:100%;text-align:left;background-color:#f5f5f5;color:#337ab7;padding:5px;border:1px solid #ddd;border-left: 0;border-right:0;">装备</div>

    <div class="panel-body" style="padding:5px;">
        <?php foreach ($ship_info['equipment'] as $equip) { ?>
            <?php
            if ($equip === null) {
                continue;
            }
            ?>
            <div class="btn btn-group btn-group-xs" style="padding:1px;">
                <span class="btn btn-primary" title="<?= $equip['desc'] ?>"><?= $equip['title'] ?></span>
                <?php if (isset($equip['num'])) { ?>
                    <span class="btn btn-info" style="color:black;"><?= $equip['num'] ?>/<?= $equip['max'] ?></span>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <div style="width:100%;text-align:left;background-color:#f5f5f5;color:#337ab7;padding:5px;border:1px solid #ddd;border-left: 0;border-right:0;">技能</div>

    <div class="panel-body" style="padding:5px;">
        <?php if (!empty($ship_info['skill'])) { ?>
            <span class="btn btn-primary btn-xs" title="<?= $ship_info['skill']['desc'] ?>"><?= $ship_info['skill']['title'] ?> Lv<?= $ship_info['skill']['level'] ?></span>
        <?php } ?>
    </div>

    <div style="width:100%;text-align:left;background-color:#f5f5f5;color:#337ab7;padding:5px;border:1px solid #ddd;border-left: 0;border-right:0;">战术</div>

    <div class="panel-body" style="padding:5px;">
        <?php foreach ($ship_info['tactics'] as $tactic) { ?>
            <?php $class = (isset($tactic['in_use']) && $tactic['in_use'] == 1 ? 'primary' : 'default'); ?>
            <span class="btn btn-<?= $class ?> btn-xs" title="<?= $tactic['desc'] ?>"><?= $tactic['title'] ?> Lv<?= $tactic['level'] ?></span>
        <?php } ?>
    </div>
</div>