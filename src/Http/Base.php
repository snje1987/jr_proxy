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

        $this->decode_header($header_string);
        return true;
    }

    protected function decode_header($header_string) {
        $http_data = [
            'header' => [],
        ];

        $pos = strpos($header_string, "\r\n");
        if ($pos === false) {
            throw new Exception('request error');
        }
        $line = substr($header_string, 0, $pos);
        $header_string = substr($header_string, $pos + 2);

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

        $this->http_data = $http_data;
        
        return $line;
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
