<?php

namespace App\JrApi\Dock;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\ShipList;

class DismantleBoat extends BaseJrApi {

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

        if (!isset($json['delShips'])) {
            return;
        }

        $ids = $json['delShips'];

        $ship_list_obj = new ShipList();
        $ship_list_obj->del_ships($ids);
    }

}
