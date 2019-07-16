<!DOCTYPE html>
<html>
    <head>
        <title>强化计算</title>
        <?php include __DIR__ . '/inc/header.php' ?>
    </head>
    <body>
        <div class="container-fluid" style="margin-top:20px;">
            <p>全局代理端口：http://[本机局域网IP]:<?= $proxy_port ?></p>
            <p>自动代理路径：http://[本机脑局域网IP]:<?= $web_port ?>/proxy</p>
            <p><a href="/boat/index">强化计算器</a></p>
        </div>
    </body>
</html>
