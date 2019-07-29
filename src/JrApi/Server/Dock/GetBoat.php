<?php

namespace App\JrApi\Server\Dock;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\PlayerInfo;

class GetBoat extends BaseJrApi {

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

        if (!isset($json['shipVO'])) {
            return;
        }

        $player_info = new PlayerInfo($this->uid);
        $player_info->set_ship($json['shipVO'])->save();
    }

}
