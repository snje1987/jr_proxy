<?php

namespace App\JrApi\Version\Index;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\GameInfo;
use App\Config;

class CheckVer extends BaseJrApi {

    protected static $fhx = 1;

    public static function init_cfg() {
        self::$fhx = \App\Config::get('main', 'fhx', 1);
    }

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

        $http_data = $response->get_http_data();
        $body = $response->get_body();

        if (isset($http_data['gzip']) && $http_data['gzip'] == true) {
            $json = zlib_decode($body);
        }
        else {
            $json = $body;
        }

        $data = json_decode($json, true);

        if (Config::get('main', 'fhx', 1) != 0) {
            if (isset($data['cheatsCheck'])) {
                $data['cheatsCheck'] = 1;
            }
            if (isset($data['ResUrl'])) {
                $data['ResUrl'] = str_replace('censor', '2', $data['ResUrl']);
            }
            echo "反和谐开启成功\n";
        }

        $data_version = isset($data['DataVersion']) ? strval($data['DataVersion']) : '';
        if ($data_version !== '') {
            $game_info = new GameInfo();
            $game_info->update_check($data_version);
        }

        $json = json_encode($data);

        if (isset($http_data['gzip']) && $http_data['gzip'] == true) {
            $body = zlib_encode($json, ZLIB_ENCODING_GZIP);
        }
        else {
            $body = $json;
        }

        $response->set_body($body);
    }

}
