<?php

namespace App\JrApi;

use App\Http;

class Fhx extends BaseJrApi {

    protected static $fhx = 1;

    public static function init_cfg() {
        self::$fhx = \App\Config::get('main', 'fhx', 1);
    }

    public function __construct() {
        
    }

    /**
     * 收到响应后执行
     * 
     * @param Http\Response $response
     * @return Http\Response
     */
    public function after($response) {

        if (self::$fhx != 1) {
            return;
        }

        $http_data = $response->get_http_data();
        $body = $response->get_body();

        if (isset($http_data['gzip']) && $http_data['gzip'] == true) {
            $data = zlib_decode($body);
        }
        else {
            $data = $body;
        }

        if (strpos($data, '"cheatsCheck":0') !== false) {
            $data = str_replace('"cheatsCheck":0', '"cheatsCheck":1', $data);
            $data = str_replace('censor', '2', $data);
            echo "反和谐开启成功\n";
        }

        if (isset($http_data['gzip']) && $http_data['gzip'] == true) {
            $body = zlib_encode($data, ZLIB_ENCODING_GZIP);
        }
        else {
            $body = $data;
        }

        $response->set_body($body);
    }

}

Fhx::init_cfg();
