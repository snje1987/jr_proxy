<?php

namespace App\JrApi\Login\Index;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\LoginInfo;

class HmLogin extends BaseJrApi {

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

        if (!isset($json['userId']) || !isset($json['hf_skey'])) {
            return;
        }
        
        LoginInfo::get()->update($json['userId'], $json['hf_skey']);
    }

}
