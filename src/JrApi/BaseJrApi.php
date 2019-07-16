<?php

namespace App\JrApi;

use App\Http;
use App\Config;

class BaseJrApi {

    /**
     * @var Http\Request 
     */
    protected $request;

    /**
     * @var Http\Response 
     */
    protected $response;

    public static function get_api_name($http_data) {
        $host = $http_data['host'];
        $url = $http_data['url'];

        if ($host === 'version.jr.moefantasy.com' ||
                $host === 'version.channel.jr.moefantasy.com') {
            $top_name = 'version';
        }
        elseif ($host === 'login.jr.moefantasy.com') {
            $top_name = 'login';
        }
        elseif (preg_match('/s[0-9]+\.jr\.moefantasy\.com/', $host)) {
            $top_name = 'server';
        }

        if (preg_match('/^\/?(\w+)\/(\w+).*$/', $url, $matches)) {
            $space_name = $matches[1];
            $class_name = $matches[2];

            return [$top_name, $space_name, $class_name];
        }
        return null;
    }

    /**
     * @param Http\Request $request
     * @return self
     */
    public static function create($request) {
        $http_data = $request->get_http_data();

        $api_naem = self::get_api_name($http_data);
        if ($api_naem === null) {
            return null;
        }

        $class_name = ucfirst($api_naem[0]) . '\\' . ucfirst($api_naem[1]) . '\\' . ucfirst($api_naem[2]);

        $full_name = __NAMESPACE__ . '\\' . $class_name;
        if (class_exists($full_name)) {
            return new $full_name($request);
        }
        return new self($request);
    }

    public function __construct($request) {
        $this->request = $request;
    }

    /**
     * 请求发送前执行
     * 
     * @param Http\Request $request
     */
    public function before() {
        
    }

    /**
     * 收到响应后执行
     * 
     * @param Http\Response $response
     */
    public function after($response) {
        $this->response = $response;
        if (Config::get('debug', 'save_api_transmission', 0) != 1) {
            return;
        }

        $dir = APP_TMP_DIR . '/trans/';
        $api = self::get_api_name($this->request->get_http_data());

        if ($api === null) {
            return;
        }

        $dir .= $api[0] . '/' . $api[1] . '/';
        $file_name = $api[2];

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $header = $this->request->get_info() . "\r\n";

        $header .= $this->request->get_request() . "\r\n==========================\r\n";

        $header .= $this->response->get_header() . "\r\n";

        file_put_contents($dir . $file_name . '.txt', $header);

        $body = $this->response->get_body();

        $str = @zlib_decode($body);
        if ($str === false) {
            $str = $body;
        }
        $json = json_decode($str);

        if ($json !== null) {
            $str = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents($dir . $file_name . '.json', $str);
        }
    }

}
