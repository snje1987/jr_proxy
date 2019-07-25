<?php

namespace App\JrApi\Version\Res;

use App\Http;
use App\JrApi\BaseJrApi;
use App\Model\GameInfo;
use App\Config;

class Download extends BaseJrApi {

    const CACHE_DIR = APP_TMP_DIR . '/cache/';

    protected $cache_file = null;

    public function __construct($request) {
        parent::__construct($request);
    }

    public function before() {
        parent::before();

        if (Config::get('main', 'cache_res', 1) != 1) {
            return;
        }

        $http_data = $this->request->get_http_data();
        $url = $http_data['url'];

        $file_path = '';

        if (preg_match('/^\/(\w+)\/warshipgirlsr\.manifest\.gz\?v=(\w+)$/', $url, $matches)) {
            $top = $matches[1];
            $v = $matches[2];

            $file_path = $top . '/warshipgirlsr.manifest.gz-' . $v;
        }
        elseif (preg_match('/^\/([^?]+)\?md5=(\w+)$/', $url, $matches)) {
            $path = $matches[1];
            $md5 = $matches[2];

            $file_path = $path . '-' . $md5;
        }
        else {
            return;
        }

        $this->cache_file = self::CACHE_DIR . str_replace('..', '', $file_path);

        if (file_exists($this->cache_file) && filesize($this->cache_file) > 0) {
            $body = file_get_contents($this->cache_file);
            if ($body !== false) {

                $http_data = [
                    'line' => 'HTTP/1.1 200 OK',
                    'code' => 200,
                    'header' => [
                        'Content-Type: application/octet-stream'
                    ],
                ];

                $response = new Http\Response();
                $response->set_http_data($http_data);
                $response->set_body($body);

                return $response;
            }
        }
    }

    /**
     * 收到响应后执行
     * 
     * @param Http\Response $response
     * @return Http\Response
     */
    public function after($response) {
        if (Config::get('main', 'cache_res', 1) != 1) {
            return;
        }

        if ($this->cache_file !== null) {
            $body = $response->get_body();
            $dir = dirname($this->cache_file);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($this->cache_file, $body);
        }
    }

}
