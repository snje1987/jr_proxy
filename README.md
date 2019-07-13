使用方法
=========

1. 需要先安装和配置好`php(>7.0)`及`compoer`环境，程序未使用数据库，不需要`mysql`。
2. 下载代码，解压到`jr_proxy`目录。
3. 进入该目录，运行命令 `php composer update`。
4. 把目录中的`config.sample.php`复制一份，并重命名为`config.php`，如需要，可以适当修改其中的配置选项；注意，不要使用windows自带的记事本来修改该文件。
5. 运行完成后，运行命令 `./start.sh`(linux) 或者 `start_for_win.bat`(windows) 来启动程序。
6. 使用该程序时，为了获取游戏内的信息，需要给手机或者模拟器设置自动代理，代理地址为：`http://[运行本程序的计算机的IP]:[您设置的web端口，默认14200]/proxy`
7. 用浏览器打开地址`http://127.0.0.1:[您设置的web端口，默认14200]`
