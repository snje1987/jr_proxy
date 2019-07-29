<?php

namespace App\JrApi\Server\Api;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\PlayerInfo;

class InitGame extends BaseJrApi {

    public function __construct($request) {
        parent::__construct($request);
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

        $body = $this->response->get_body();

        $str = zlib_decode($body);
        $json = json_decode($str, true);

        if ($json === null) {
            return;
        }

        if (!isset($json['userShipVO'])) {
            return;
        }

        $ship_list = $json['userShipVO'];

        $player_info = new PlayerInfo($this->uid, true);
        $player_info->set_ships($ship_list);

        if (isset($json['tactics'])) {
            $player_info->set_tactics($json['tactics']);
        }

        if (isset($json['fleetVo'])) {
            $player_info->set_fleet($json['fleetVo']);
        }

        $player_info->save();
    }

}
