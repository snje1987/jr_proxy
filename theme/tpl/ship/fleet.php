<!DOCTYPE html>
<html>
    <head>
        <title>战斗记录</title>
        <?php include APP_TPL_DIR . '/inc/header.php' ?>
    </head>
    <body>
        <?php $cur_tab = '/ship/fleet'; ?>
        <?php include APP_TPL_DIR . '/inc/common.php' ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-3 col-xs-2">
                    <div style="margin-bottom:10px;">
                        <select name="uid">
                            <?php foreach ($uid_list as $uid) { ?>
                                <option value="<?= $uid ?>"<?= $uid == $cur_uid ? ' selected' : '' ?>><?= $uid ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">文件列表</div>
                        <div class="list-group">
                            <?php foreach ($fleet_list as $k => $name) { ?>
                                <a class="list-group-item<?= $cur_fleet == $k ? ' active' : '' ?>" href="/ship/fleet?uid=<?= $cur_uid ?>&fleet=<?= $k ?>">[<?= $k ?>队]<?= $name ?></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-9 col-xs-10">
                    <div class="row">
                        <?php if (!empty($ship_list)) { ?>
                            <?php foreach ($ship_list as $k => $ship) { ?>
                                <?php if ($k % 3 == 0 && $k != 0) { ?>
                                </div><div class="row">
                                <?php } ?>
                                <div class="col-sm-4" style="padding:2px;">
                                    <?php include APP_TPL_DIR . '/inc/ship_card.php' ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            (function ($) {
                $(document).ready(function () {
                    $('select[name="uid"]').change(function () {
                        location.href = '/ship/fleet?uid=' + $(this).val();
                    });
                });
            })(jQuery);
        </script>
    </body>
</html>
