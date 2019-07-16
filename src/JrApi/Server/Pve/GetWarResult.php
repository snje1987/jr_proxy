<?php

namespace App\JrApi\Server\Pve;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\PlayerInfo;
use App\Model\CurrentWar;
use App\Config;

class GetWarResult extends BaseJrApi {

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

        $body = $this->response->get_body();

        $str = zlib_decode($body);
        $json = json_decode($str, true);

        if ($json === null) {
            return;
        }

        if (!isset($json['warResult'])) {
            return;
        }

        if (Config::get('main', 'war_log', 0) == 1) {
            $current_war = new CurrentWar();
            $current_war->set_result($json)->save_to('pve');
        }

        if (isset($json['newShipVO']) && isset($json['newShipVO'][0])) {
            $new_ship = $json['newShipVO'][0];

            $id = $new_ship['id'];
            $ship = [
                'title' => $new_ship['title'],
                'shipCid' => $new_ship['shipCid'],
                'isLocked' => $new_ship['isLocked'],
                'strengthenAttribute' => $new_ship['strengthenAttribute'],
            ];

            $player_info = new PlayerInfo();
            $player_info->set_ship($id, $ship);
        }
    }

}
