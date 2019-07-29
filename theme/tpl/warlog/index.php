<!DOCTYPE html>
<html>
    <head>
        <title>战斗记录</title>
        <?php include APP_TPL_DIR . '/inc/header.php' ?>
    </head>
    <body>
        <?php $cur_tab = '/warlog/index'; ?>
        <?php include APP_TPL_DIR . '/inc/common.php' ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-3 col-xs-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">文件列表</div>
                        <div class="list-group">
                            <?php foreach ($dir_list as $k => $item) { ?>
                                <a class="list-group-item" href="/warlog/index?p=<?= urlencode($item) ?>"><?= $k ?>/</a>
                            <?php } ?>
                            <?php foreach ($file_list as $k => $item) { ?>
                                <a class="list-group-item<?= $item === $cur_file ? ' active' : '' ?>" href="/warlog/index?p=<?= urlencode($item) ?>"><?= $k ?></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-9 col-xs-10">
                    <?php if (!empty($path)) { ?>
                        <ol class="breadcrumb">
                            <?php foreach ($path as $k => $v) { ?>
                                <li><a href="/warlog/index?p=<?= urlencode($v) ?>"><?= $k ?></a></li>
                            <?php } ?>
                        </ol>
                    <?php } ?>
                    <?php if ($log !== false) { ?>
                        <div style="margin-bottom:5px;">
                            <a class="btn btn-primary ajax" ohref="/warlog/replay?p=<?= urlencode($cur_file) ?>">设置回放</a>
                        </div>
                        <?php include APP_TPL_DIR . '/inc/warlog_detail.php' ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </body>
</html>
