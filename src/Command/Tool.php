<?php

namespace App\Command;

class Tool extends BaseCommand {

    /**
     * password_hash
     */
    public function cmd_password_hash($args) {
        $pwd = isset($args[0]) ? strval($args[0]) : '';
        if (empty($pwd)) {
            echo "密码不能为空\n";
        }
        echo password_hash($pwd, PASSWORD_DEFAULT) . "\n";
    }

    /**
     * phpinfo
     */
    public function cmd_phpinfo($args) {
        phpinfo();
    }

    /**
     * date
     */
    public function cmd_date($args) {
        $timestamp = isset($args[0]) ? intval($args[0]) : 0;

        $format = isset($args[1]) ? strval($args[1]) : 'Y-m-d H:i:s';

        echo date($format, $timestamp) . "\n";
    }

    /**
     * base64_encode
     */
    public function cmd_base64_encode($args) {
        $str = isset($args[0]) ? strval($args[0]) : '';
        echo base64_encode($str) . "\n";
    }

    /**
     * base64_encode
     */
    public function cmd_base64_decode($args) {
        $str = isset($args[0]) ? strval($args[0]) : '';
        echo base64_encode($str) . "\n";
    }

    /**
     * 输出程序当前配置
     */
    public function cmd_show_config($args) {
        print_r(\App\Config::get());
    }

}
