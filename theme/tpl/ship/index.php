<!DOCTYPE html>
<html>
    <head>
        <title>强化计算</title>
        <?php include APP_TPL_DIR . '/inc/header.php' ?>
    </head>
    <body>
        <?php include __DIR__ . '/../inc/common.php' ?>
        <div class="container-fluid">
            <div class="row">
                <select name="uid">
                    <?php foreach ($uid_list as $uid) { ?>
                        <option value="<?= $uid ?>"<?= $uid == $cur_uid ? ' selected' : '' ?>><?= $uid ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="row">
                <div class="col-sm-6 col-xs-12"><h3>强化目标</h3></div>
                <div class="col-sm-6 col-xs-12"> <h3>可用素材</h3></div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-xs-12" style="height:400px;overflow-y:auto;">
                    <table class="table table-bordered table-hover table-condensed table-striped">
                        <tr>
                            <td width="50" class="text-center">选择</td>
                            <td class="text-center">名称</td>
                            <td class="text-center">火力(x<?= $points[0] ?>)</td>
                            <td class="text-center">鱼雷(x<?= $points[1] ?>)</td>
                            <td class="text-center">装甲(x<?= $points[2] ?>)</td>
                            <td class="text-center">对空(x<?= $points[3] ?>)</td>
                        </tr>
                        <?php foreach ($target as $id => $v) { ?>
                            <tr>
                                <td class="text-center"><input type="radio" name="target" value="<?= $id ?>" /></td>
                                <td class="text-center"><?= $v['title'] ?></td>
                                <td class="text-center"><?= $v['strengthenAttribute']['atk'] ?> / <?= $v['strengthenTop']['atk'] ?></td>
                                <td class="text-center"><?= $v['strengthenAttribute']['torpedo'] ?> / <?= $v['strengthenTop']['torpedo'] ?></td>
                                <td class="text-center"><?= $v['strengthenAttribute']['def'] ?> / <?= $v['strengthenTop']['def'] ?></td>
                                <td class="text-center"><?= $v['strengthenAttribute']['air_def'] ?> /<?= $v['strengthenTop']['air_def'] ?> </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <div class="col-sm-6 col-xs-12" style="height:400px;overflow-y:auto;">

                    <table class="table table-bordered table-hover table-condensed table-striped">
                        <tr>
                            <td width="50" class="text-center"><input type="checkbox" name="check_all" /></td>
                            <td class="text-center">名称</td>
                            <td class="text-center">数量</td>
                            <td class="text-center">口感</td>
                            <td class="text-center">拆解</td>
                        </tr>
                        <?php foreach ($material as $ship_id => $v) { ?>
                            <tr>
                                <td class="text-center"><input type="checkbox" name="material" value="<?= $ship_id ?>" /></td>
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
            <div class="text-center" style="margin-top:20px">
                <button class="btn btn-primary" id="calc" ohref="/boat/calc">开始计算 [ 资源价值比 <?= $values[0] ?> / <?= $values[1] ?> / <?= $values[2] ?> / <?= $values[3] ?> ]</button>
            </div>
            <div style="height:30px;"></div>
        </div>
        <script type="text/javascript">
            (function ($) {
                var get_data = function () {
                    var target = $('input[name="target"]:checked').val();
                    if (typeof target === 'undefined') {
                        alert('必须选择强化目标');
                        return null;
                    }
                    var material = [];
                    var checks = $('input[name="material"]');
                    for (var i = 0; i < checks.length; i++) {
                        if (checks.eq(i).is(':checked')) {
                            material[material.length] = checks.eq(i).val();
                        }
                    }

                    if (material.length <= 0) {
                        alert('必须选择强化素材');
                        return null;
                    }

                    var data = {};
                    data.target = target;
                    data.material = JSON.stringify(material);
                    data.uid = '<?= $cur_uid ?>';

                    return data;
                };
                $(document).ready(function () {
                    $('#calc').pop_btn({
                        target: '#pop',
                        data: get_data
                    });

                    $('input[name="check_all"]').check_all({target: 'input[name="material"]'});
                    $('select[name="uid"]').change(function () {
                        location.href = '/boat/index?uid=' + $(this).val();
                    });
                });
            })(jQuery);
        </script>
    </body>
</html>
