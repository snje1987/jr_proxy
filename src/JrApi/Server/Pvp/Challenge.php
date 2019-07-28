<?php

namespace App\JrApi\Server\Pvp;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\CurrentWar;
use App\Config;
use App\Model\WarReplayer;

class Challenge extends BaseJrApi {

    public function __construct($request) {
        parent::__construct($request);
    }

    public function before() {
        parent::before();

        if (Config::get('main', 'war_replay', 0) != 1) {
            return;
        }

        if ($this->uid === null) {
            return;
        }

        $http_data = $this->request->get_http_data();
        $url = $http_data['url'];

        $prefix = '/pvp/challenge/' . $this->uid . '/';
        if (strncmp($url, $prefix, strlen($prefix)) == 0) {
            $current_war = new CurrentWar($this->uid);
            $current_war->set_type('replay')->save();

            $war_replayer = new WarReplayer($this->uid);
            return $war_replayer->do_replay('challenge');
        }
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

        if (!isset($json['warReport'])) {
            return;
        }

        $http_data = $this->request->get_http_data();
        $url = $http_data['url'];

        $current_war = new CurrentWar($this->uid);
        if (preg_match('/^\/pvp\/challenge\/(\d+)\/.*$/', $url, $matches)) {
            $current_war->set_name($matches[1]);
        }
        $current_war->set_day($json)->save();
    }

}
