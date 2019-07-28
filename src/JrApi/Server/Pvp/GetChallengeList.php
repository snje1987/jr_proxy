<?php

namespace App\JrApi\Server\Pvp;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Config;
use App\Model\WarReplayer;

class GetChallengeList extends BaseJrApi {

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

        if (Config::get('main', 'war_replay', 0) != 1) {
            return;
        }

        $body = $this->response->get_body();

        $str = zlib_decode($body);
        $json = json_decode($str, true);

        if ($json === null) {
            return;
        }

        if (!isset($json['list'])) {
            return;
        }

        $fack = $json['list'][0];

        $fack = WarReplayer::create_fack_user($fack, $this->uid);

        array_push($json['list'], $fack);

        $str = json_encode($json);
        $body = zlib_encode($str, ZLIB_ENCODING_GZIP);
        $this->response->set_body($body);
    }

}
