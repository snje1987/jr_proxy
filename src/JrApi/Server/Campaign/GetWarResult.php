<?php

namespace App\JrApi\Server\Campaign;

use App\Http;
use App\JrApi\BaseJrApi;
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

        if (!isset($json['warResult'])) {
            return;
        }

        $current_war = new CurrentWar($this->uid);
        $current_war->set_result($json)->save_log();
    }

}