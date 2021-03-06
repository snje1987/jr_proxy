<?php

namespace App\JrApi\Server\Boat;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\PlayerInfo;

class InstantRepairAll extends BaseJrApi {

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

        if (!isset($json['shipVOs'])) {
            return;
        }

        $player_info = new PlayerInfo($this->uid);
        $player_info->set_ships($json['shipVOs'])->save();
    }

}
