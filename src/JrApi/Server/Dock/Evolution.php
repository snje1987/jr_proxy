<?php

namespace App\JrApi\Server\Dock;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\PlayerInfo;

class Evolution extends BaseJrApi {

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

        if (!isset($json['shipVO']) || !isset($json['shipVO'][0])) {
            return;
        }



        $new_ship = $json['shipVO'][0];

        $id = $new_ship['id'];
        $ship = [
            'title' => $new_ship['title'],
            'shipCid' => $new_ship['shipCid'],
            'isLocked' => $new_ship['isLocked'],
            'strengthenAttribute' => $new_ship['strengthenAttribute'],
        ];

        $player_info = PlayerInfo::get();
        $player_info->set_ship($id, $ship);
    }

}
