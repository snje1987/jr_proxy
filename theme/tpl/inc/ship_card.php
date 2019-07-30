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
?>
<div class="panel panel-primary">
    <div class="panel-heading" style="padding:5px;"><?= $ship['title'] ?></div>

    <div class="panel-body" style="padding:5px;">
        <div class="btn btn-group btn-group-xs" style="padding:1px;">
            <span class="btn btn-primary">类型</span>
            <span class="btn btn-info" style="color:black;min-width:60px;text-align:right;"><?= $ship['type'] ?></span>
        </div>
        <?php if (isset($ship['ori_title'])) { ?>
            <div class="btn btn-group btn-group-xs" style="padding:1px;">
                <span class="btn btn-primary">图鉴名称</span>
                <span class="btn btn-info" style="color:black;min-width:60px;text-align:right;"><?= $ship['ori_title'] ?></span>
            </div>
        <?php } ?>
    </div>

    <div style="width:100%;text-align:left;background-color:#f5f5f5;color:#337ab7;padding:5px;border:1px solid #ddd;border-left: 0;border-right:0;">基本属性</div>

    <div class="panel-body" style="padding:5px;">
        <?php foreach ($basic_props as $k => $name) { ?>
            <?php
            if (!isset($ship[$k])) {
                continue;
            }
            ?>
            <div class="btn btn-group btn-group-xs" style="padding:1px;">
                <span class="btn btn-primary"><?= $name ?></span>
                <span class="btn btn-info" style="color:black;min-width:60px;text-align:right;">
                    <?= is_array($ship[$k]) ? $ship[$k][0] . '+' . $ship[$k][1] . '=' . ($ship[$k][0] + $ship[$k][1]) : $ship[$k] ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div style="width:100%;text-align:left;background-color:#f5f5f5;color:#337ab7;padding:5px;border:1px solid #ddd;border-left: 0;border-right:0;">战斗属性</div>

    <div class="panel-body" style="padding:5px;">
        <div class="btn btn-group btn-group-xs" style="padding:1px;">
            <span class="btn btn-primary">耐久</span>
            <span class="btn btn-info" style="color:black;min-width:90px;text-align:right;">
                <?= $ship['hp'] ?>/<?= is_array($ship['hpMax']) ? $ship['hpMax'][0] . '+' . $ship['hpMax'][1] . '=' . ($ship['hpMax'][0] + $ship['hpMax'][1]) : $ship['hpMax'] ?>
            </span>
        </div>
        <?php foreach (App::SHIP_BATTLE_PROP_NAME as $k => $name) { ?>
            <?php
            if (!isset($ship[$k]) || $k == 'hp' || $k == 'hpMax') {
                continue;
            }
            ?>
            <div class="btn btn-group btn-group-xs" style="padding:1px;">
                <span class="btn btn-primary"><?= $name ?></span>
                <span class="btn btn-info" style="color:black;min-width:90px;text-align:right;">
                    <?= is_array($ship[$k]) ? $ship[$k][0] . '+' . $ship[$k][1] . '=' . ($ship[$k][0] + $ship[$k][1]) : $ship[$k] ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div style="width:100%;text-align:left;background-color:#f5f5f5;color:#337ab7;padding:5px;border:1px solid #ddd;border-left: 0;border-right:0;">装备</div>

    <div class="panel-body" style="padding:5px;">
        <?php foreach ($ship['equipment'] as $equip) { ?>
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
        <?php if (!empty($ship['skill'])) { ?>
            <span class="btn btn-primary btn-xs" title="<?= $ship['skill']['desc'] ?>"><?= $ship['skill']['title'] ?> Lv<?= $ship['skill']['level'] ?></span>
        <?php } ?>
    </div>

    <div style="width:100%;text-align:left;background-color:#f5f5f5;color:#337ab7;padding:5px;border:1px solid #ddd;border-left: 0;border-right:0;">战术</div>

    <div class="panel-body" style="padding:5px;">
        <?php foreach ($ship['tactics'] as $tactic) { ?>
            <span class="btn btn-primary btn-xs" title="<?= $tactic['desc'] ?>"><?= $tactic['title'] ?> Lv<?= $tactic['level'] ?></span>
        <?php } ?>
        <?php foreach ($ship['tactics_avl'] as $tactic) { ?>
            <span class="btn btn-default btn-xs" title="<?= $tactic['desc'] ?>"><?= $tactic['title'] ?> Lv<?= $tactic['level'] ?></span>
        <?php } ?>
    </div>
</div>