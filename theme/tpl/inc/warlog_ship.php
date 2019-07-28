
<?php

use App\Controler\Warlog;
?><tr>
    <td class="text-left">
        <div class="btn-group">
            <span class="btn btn-primary btn-xs"><?= $index ?></span>
            <span class="btn btn-<?= $class ?> btn-xs" title="<?= $ship['desc'] ?>"><?= $ship['title'] ?></span>
        </div>
    </td>
    <td class="text-left">
        <div class="btn-group" style="margin-bottom:3px;">
            <span class="btn btn-primary btn-xs">等级</span>
            <span class="btn btn-info btn-xs" style="width:90px;text-align:right;color:black;"><?= $ship['level'] ?></span>
        </div>
        <div class="btn-group" style="margin-bottom:3px;">
            <span class="btn btn-primary btn-xs">耐久</span>
            <span class="btn btn-info btn-xs" style="width:90px;text-align:right;color:black;"><?= $ship['hp'] ?>/<?= $ship['hpMax'] ?></span>
        </div>
        <br />
        <?php $col = 1; ?>
        <?php foreach (Warlog::SHIP_ATTR_NAME as $k => $v) { ?>
            <?php if (isset($ship[$k])) { ?>
                <div class="btn-group" style="margin-bottom:3px;">
                    <span class="btn btn-primary btn-xs"><?= $v ?></span>
                    <span class="btn btn-info btn-xs" style="width:90px;text-align:right;color:black;">
                        <?php
                        if (is_array($ship[$k])) {
                            echo $ship[$k][0] . ($ship[$k][1] > 0 ? '+' . $ship[$k][1] : $ship[$k][1]) . '=' . ($ship[$k][0] + $ship[$k][1]);
                        }
                        else {
                            echo $ship[$k];
                        }
                        ?>                        
                    </span>
                </div>
            <?php } ?>
            <?php if ($col ++ % 2 == 0) { ?>
                <br />
            <?php } ?>
        <?php } ?>
    </td>
    <td class="text-left">
        <?php foreach ($ship['equip'] as $v) { ?>
            <span class="btn btn-primary btn-xs" title="<?= $v['desc'] ?>"><?= $v['title'] ?></span><br />
        <?php } ?>
    </td>
    <td class="text-left">
        <?php if ($ship['skill'] !== null) { ?>
            <span class="btn btn-primary btn-xs" title="<?= $ship['skill']['desc'] ?>"><?= $ship['skill']['title'] ?> Lv<?= $ship['skill']['level'] ?></span>
        <?php } ?>
    </td>
    <td class="text-left">
        <?php foreach ($ship['tactics'] as $v) { ?>
            <span class="btn btn-primary btn-xs" title="<?= $v['desc'] ?>"><?= $v['title'] ?> Lv<?= $v['level'] ?></span><br />
        <?php } ?>
    </td>
</tr>
