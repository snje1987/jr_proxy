<?php

namespace App\JrApi;

use App\Http;

class BaseJrApi {

    protected static $save_api_transmission = 0;

    /**
     * @var Http\Request 
     */
    protected $request;

    /**
     * @var Http\Response 
     */
    protected $response;

    public static function init_cfg() {
        self::$save_api_transmission = \App\Config::get('debug', 'save_api_transmission', 0);
    }

    /**
     * 
     * @return self
     */
    public static function create($api) {
        if (!isset($api[0])) {
            return new self();
        }

        $class_name = ucfirst($api[0]);
        if (isset($api[1])) {
            $class_name .= '\\' . ucfirst($api[1]);
        }

        $full_name = __NAMESPACE__ . '\\' . $class_name;
        if (class_exists($full_name)) {
            return new $full_name();
        }
        return new self();
    }

    public function __construct() {
        
    }

    /**
     * 请求发送前执行
     * 
     * @param Http\Request $request
     */
    public function before($request) {
        $this->request = $request;
    }

    /**
     * 收到响应后执行
     * 
     * @param Http\Response $response
     */
    public function after($response) {
        $this->response = $response;
        if (self::$save_api_transmission != 1) {
            return;
        }

        $dir = APP_ROOT . \App\Config::get('main', 'tmp', '/tmp') . '/transmission/';
        $api = $this->request->get_api();

        if (isset($api[1])) {
            $dir .= $api[0] . '/';
            $file_name = $api[1];
        }
        else {
            $file_name = $api[0];
        }

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $header = $this->request->get_info() . "\r\n";

        $header .= $this->request->get_request() . "\r\n==========================\r\n";

        $header .= $this->response->get_header() . "\r\n";

        file_put_contents($dir . $file_name . '.txt', $header);

        $body = $this->response->get_body();

        $str = zlib_decode($body);
        $json = json_decode($str);

        if ($json !== null) {
            $str = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents($dir . $file_name . '.json', $str);
        }
    }

}

BaseJrApi::init_cfg();
