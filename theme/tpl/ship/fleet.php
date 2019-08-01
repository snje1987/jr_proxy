<!DOCTYPE html>
<html>
    <head>
        <title>编队辅助</title>
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
                    <?php if ($cur_fleet > 0) { ?>
                        <?php include APP_TPL_DIR . '/inc/fleet_card.php' ?>
                    <?php } ?>
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
