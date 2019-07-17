<?php

namespace App\Http;

use Workerman\Connection\TcpConnection;
use Exception;

class Request extends Base {

    public $ukey = null;

    public function __construct() {
        parent::__construct();
    }

    public function init($buffer) {
        if (!parent::init($buffer)) {
            return false;
        }

        if ($this->http_data['type'] !== 'request') {
            throw new Exception('bad request');
        }

        return true;
    }

    public function get_addr() {
        $addr = "{$this->http_data['host']}:{$this->http_data['port']}";
        return $addr;
    }

    public function get_request() {
        $http_data = $this->http_data;
        $header = implode("\r\n", $http_data['header']);

        if ($http_data['method'] == 'POST') {
            $header .= "\r\nContent-Length: " . $http_data['length'];
        }

        $request = "{$http_data['method']} {$http_data['url']} {$http_data['http']}\r\n{$header}\r\n\r\n";

        if ($this->body !== '') {
            $request .= $this->body;
        }

        return $request;
    }

    public function get_info() {
        $request = $this->http_data['method'] . ' http://' . $this->http_data['host'] . ':' . $this->http_data['port'] . $this->http_data['url'];
        return $request;
    }

    protected function decode_header($header_string) {
        $line = parent::decode_header($header_string);

        $info = explode(' ', $line);
        if (count($info) < 3) {
            throw new Exception('request error');
        }

        $http_data = $this->http_data;

        if (strncasecmp($info[0], 'http', 4) === 0) {
            throw new Exception('非法请求');
        }

        $http_data['type'] = 'request';
        if ($info[0] !== 'GET' && $info[0] !== 'POST') {
            throw new Exception('request error');
        }

        if ($info[0] === 'GET') {
            $http_data['length'] = 0;
        }

        if ($http_data['length'] === -1) {
            throw new Exception('request error');
        }

        $url = $info[1];
        $matches = array();
        if (!preg_match('/^(http):\/\/([a-z0-9-_.]+)(:(\d+))?(\S*)$/', $url, $matches)) {
            return false;
        }
        $http_data['method'] = $info[0];
        $http_data['url'] = $matches[5];
        $http_data['host'] = $matches[2];
        if ($matches[4] !== '') {
            $http_data['port'] = $matches[4];
        }
        else {
            $http_data['port'] = 80;
        }

        $http_data['http'] = $info[2];

        foreach ($http_data['header'] as $k => $v) {
            if (strcasecmp($v, 'Connection: Keep-alive') == 0) {
                $http_data['header'][$k] = 'Connection: close';
            }
            elseif (preg_match('/^Cookie:.*? hf_skey=([a-z0-9.]+);.*$/', $v, $matches)) {
                $this->ukey = $matches[1];
            }
        }

        $this->http_data = $http_data;
    }

}
