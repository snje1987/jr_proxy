<?php

namespace App\JrApi\Server\Pvp;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\CurrentWar;
use App\Config;
use App\Model\WarReplayer;

class GetWarResult extends BaseJrApi {

    public function __construct($request) {
        parent::__construct($request);
    }

    public function before() {
        parent::before();

        if (Config::get('main', 'war_replay', 0) != 1) {
            return;
        }

        if ($this->uid === null) {
            return;
        }

        $current_war = new CurrentWar($this->uid);

        $type = $current_war->get_type();
        if ($type == 'replay') {

            $http_data = $this->request->get_http_data();
            $url = $http_data['url'];

            $prefix = '/pvp/getWarResult/1/';
            if (strncmp($url, $prefix, strlen($prefix)) == 0) {
                $extra = true;
            }
            else {
                $extra = false;
            }

            $war_replayer = new WarReplayer($this->uid);
            return $war_replayer->do_replay('result', $extra);
        }
    }

    /**
     * 收到响应后执行
     * 
     * @param Http\Response $response
     * @return Http\Response
     */
    public function after($response) {

        parent::after($response);

        if ($this->uid === null) {
            return;
        }

        if (Config::get('main', 'war_log', 0) != 1) {
            return;
        }

        $body = $this->response->get_body();

        $str = zlib_decode($body);
        $json = json_decode($str, true);

        if ($json === null) {
            return;
        }

        if (isset($json['warResult'])) {
            $current_war = new CurrentWar($this->uid);
            $current_war->set_result($json)->save_log();
        }

        if (isset($json['shipVO'])) {
            $player_info = new \App\Model\PlayerInfo($this->uid);
            $player_info->set_ships($json['shipVO'])->save();
        }
    }

}
