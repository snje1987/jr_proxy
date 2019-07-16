<?php

namespace App\JrApi\Api;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\ShipList;

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

        $list = [];

        foreach ($ship_list as $v) {
            $id = $v['id'];
            $list[$id] = [
                'title' => $v['title'],
                'shipCid' => $v['shipCid'],
                'isLocked' => $v['isLocked'],
                'strengthenAttribute' => $v['strengthenAttribute'],
            ];
        }

        $ship_list_obj = new ShipList();
        $ship_list_obj->set_list($list);
    }

}
