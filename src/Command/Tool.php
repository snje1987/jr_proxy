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

    public function cmd_sync_ship_card() {
        $url = 'http://login.jr.moefantasy.com:80/index/getInitConfigs/&t=' . time() . str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT) . '&gz=1&market=2&channel=100016&version=4.5.0';

        $data = file_get_contents($url);

        $data = zlib_decode($data);

        $json = json_decode($data, true);

        if (!isset($json['shipCard'])) {
            return;
        }

        $ship_card = $json['shipCard'];

        //file_put_contents(APP_ROOT . '/tmp/raw_cart.json', json_encode($ship_card, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $list = [];

        foreach ($ship_card as $ship) {
            if ($ship['npc'] != 0) {
                continue;
            }
            $id = $ship['cid'];

            $list[$id] = [
                'title' => $ship['title'],
                'dismantle' => $ship['dismantle'], //拆解
                'strengthenTop' => $ship['strengthenTop'], //强化满的消耗
                'strengthenLevelUpExp' => $ship['strengthenLevelUpExp'], //每级花费点数
                'strengthenSupplyExp' => $ship['strengthenSupplyExp'], //狗粮口感
            ];
        }

        $ship_card_obj = new \App\Model\ShipCard();
        $ship_card_obj->set_list($list);
    }

}
