<?php

namespace App\JrApi\Server\Boat;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\PlayerInfo;

class SupplyAll extends BaseJrApi {

    public function __construct($request) {
        parent::__construct($request);
    }

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

        if (!isset($json['shipMiniVO']) && !isset($json['tactics'])) {
            return;
        }

        $player_info = new PlayerInfo($this->uid);

        if (isset($json['tactics'])) {
            $player_info->set_tactics($json['tactics']);
        }

        if (isset($json['shipMiniVO'])) {
            $player_info->update_ships_res($json['shipMiniVO']);
        }

        $player_info->save();
    }

}
