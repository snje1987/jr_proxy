<!DOCTYPE html>
<html>
    <head>
        <title>强化计算</title>
        <?php include APP_TPL_DIR . '/inc/header.php' ?>
    </head>
    <body>
        <?php $cur_tab = '/'; ?>
        <?php include APP_TPL_DIR . '/inc/common.php' ?>
        <div class="container-fluid" style="margin-top:20px;">
            <p>全局代理端口：http://[本机局域网IP]:<?= $proxy_port ?></p>
            <p>自动代理路径：http://[本机脑局域网IP]:<?= $web_port ?>/proxy</p>
        </div>
    </body>
</html>
