<!DOCTYPE html>
<html>
    <head>
        <title>强化计算</title>
        <?php include __DIR__ . '/../inc/header.php' ?>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <h3>强化目标</h3>
                    <table class="table table-bordered table-hover table-condensed table-striped">
                        <tr>
                            <td width="50" class="text-center">选择</td>
                            <td class="text-center">名称</td>
                            <td class="text-center">火力</td>
                            <td class="text-center">鱼雷</td>
                            <td class="text-center">装甲</td>
                            <td class="text-center">对空</td>
                        </tr>
                        <?php foreach ($target as $id => $v) { ?>
                            <tr>
                                <td class="text-center"><input type="radio" name="target" vlaue="<?= $id ?>" /></td>
                                <td class="text-center"><?= $v['title'] ?></td>
                                <td class="text-center"><?= $v['strengthenAttribute']['atk'] ?> / <?= $v['strengthenTop']['atk'] ?></td>
                                <td class="text-center"><?= $v['strengthenAttribute']['torpedo'] ?> / <?= $v['strengthenTop']['torpedo'] ?></td>
                                <td class="text-center"><?= $v['strengthenAttribute']['def'] ?> / <?= $v['strengthenTop']['def'] ?></td>
                                <td class="text-center"><?= $v['strengthenAttribute']['air_def'] ?> /<?= $v['strengthenTop']['air_def'] ?> </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <h3>可用狗粮</h3>
                    <table class="table table-bordered table-hover table-condensed table-striped">
                        <tr>
                            <td width="50" class="text-center"><input type="checkbox" name="check_all" /></td>
                            <td class="text-center">名称</td>
                            <td class="text-center">数量</td>
                            <td class="text-center">口感</td>
                            <td class="text-center">拆解</td>
                        </tr>
                        <?php foreach ($use as $ship_id => $v) { ?>
                            <tr>
                                <td class="text-center"><input type="checkbox" name="use" vlaue="<?= $ship_id ?>" /></td>
                                <td class="text-center"><?= $v['title'] ?></td>
                                <td class="text-center"><?= $v['count'] ?></td>
                                <td class="text-center">
                                    <?= $v['strengthenSupplyExp']['atk'] ?> /
                                    <?= $v['strengthenSupplyExp']['torpedo'] ?> /
                                    <?= $v['strengthenSupplyExp']['def'] ?> /
                                    <?= $v['strengthenSupplyExp']['air_def'] ?>
                                </td>
                                <td class="text-center">
                                    <?= $v['dismantle']['2'] ?> /
                                    <?= $v['dismantle']['3'] ?> /
                                    <?= $v['dismantle']['4'] ?> /
                                    <?= $v['dismantle']['9'] ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary">开始计算 [ 资源价值比 <?= $values[0] ?> / <?= $values[1] ?> / <?= $values[2] ?> / <?= $values[3] ?> ]</button>
            </div>
            <div style="height:30px;"></div>
        </div>
        <script type="text/javascript">
            (function ($) {
                $(document).ready(function () {
                    $('input[name="check_all"]').check_all({target: 'input[name="use"]'});
                });
            })(jQuery);
        </script>
    </body>
</html>
