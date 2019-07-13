<?php

namespace App\JrApi\Boat;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\ShipList;

class ConvertSkill extends BaseJrApi {

    public function __construct() {
        
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
        
        if (!isset($json['shipVO'])) {
            return;
        }

        $new_ship = $json['shipVO'];

        $id = $new_ship['id'];
        $ship = [
            'title' => $new_ship['title'],
            'shipCid' => $new_ship['shipCid'],
            'isLocked' => $new_ship['isLocked'],
            'strengthenAttribute' => $new_ship['strengthenAttribute'],
        ];

        $ship_list_obj = new ShipList();
        $ship_list_obj->set_ship($id, $ship);
    }

}
