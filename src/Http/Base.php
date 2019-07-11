<?php

namespace App\Http;

use Exception;

abstract class Base {

    protected $buffer = '';
    protected $http_data = [];
    protected $body = '';

    public function __construct() {
        
    }

    public function get_http_data() {
        return $this->http_data;
    }

    public function set_http_data($http_data) {
        $this->http_data = $http_data;
    }

    public function get_body() {
        return $this->body;
    }

    public function set_body($body) {
        $this->body = $body;
    }

    protected function init($buffer) {
        $this->buffer .= $buffer;

        if (!$this->parse_header()) {
            return false;
        }

        if (!$this->read_content()) {
            return false;
        }

        return true;
    }

    protected function parse_header() {

        if (!empty($this->http_data)) {
            return true;
        }

        $header_end = strpos($this->buffer, "\r\n\r\n");
        if ($header_end === false) {
            return false;
        }
        $header_string = substr($this->buffer, 0, $header_end);
        $this->buffer = substr($this->buffer, $header_end + 4);


        $http_data = [
            'header' => [],
        ];

        $pos = strpos($header_string, "\r\n");
        if ($pos === false) {
            throw new Exception('request error');
        }
        $line = substr($header_string, 0, $pos);
        $header_string = substr($header_string, $pos + 2);

        $info = explode(' ', $line);
        if (count($info) < 3) {
            throw new Exception('request error');
        }

        $header_array = explode("\r\n", $header_string);

        foreach ($header_array as $v) {
            if (preg_match('/^Content-Length:\s*(\d+)\s*$/i', $v, $matches)) {
                $http_data['length'] = intval($matches[1]);
            }
            elseif (strcasecmp($v, 'Transfer-Encoding: chunked') == 0) {
                $http_data['length'] = -2;
            }
            elseif (strcasecmp($v, 'Content-Encoding: gzip') == 0) {
                $http_data['gzip'] = true;
                $http_data['header'][] = $v;
            }
            else {
                $http_data['header'][] = $v;
            }
        }

        if (!isset($http_data['length'])) {
            $http_data['length'] = -1;
        }

        if (strncasecmp($info[0], 'http', 4) === 0) {//http响应
            $http_data['line'] = $line;
            $http_data['type'] = 'response';
            $http_data['code'] = intval($info[1]);
            if ($http_data['length'] === -1 && $http_data['code'] != 200) {//不是200则没有响应内容
                $http_data['length'] = 0;
            }
            $this->http_data = $http_data;
            return true;
        }
        else {//http请求
            $http_data['type'] = 'request';
            if ($info[0] === 'GET' || $info[0] === 'POST') {
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
                }

                $this->http_data = $http_data;
                return true;
            }
            else {
                throw new Exception('request error');
            }
        }
    }

    protected function read_content() {
        $data_len = $this->http_data['length'];

        if ($data_len >= 0) {
            $len = strlen($this->buffer);
            if ($len >= $this->http_data['length']) {
                $this->body = $this->buffer;
                $this->buffer = '';
                return true;
            }
        }
        elseif ($data_len === -2) {
            while (true) {
                $pos = strpos($this->buffer, "\r\n", 0);
                if ($pos === false) {
                    return false;
                }

                $len_text = substr($this->buffer, 0, $pos);
                $len = hexdec($len_text);

                if ($len === 0) {//最后一个chunk
                    $this->buffer = '';
                    return true;
                }

                if ($pos + 4 + $len <= strlen($this->buffer)) {
                    $block = substr($this->buffer, $pos + 2, $len);
                    $this->body .= $block;

                    $this->buffer = substr($this->buffer, $pos + $len + 4);
                }
                else {
                    return false;
                }
            }
        }
        else {
            throw new Exception('request error');
        }
        return false;
    }

}
