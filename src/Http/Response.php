<?php

namespace App\Http;

use App\Http;
use Exception;

class Response extends Base {

    public function __construct() {
        parent::__construct();
    }

    public function init($buffer) {
        if (!parent::init($buffer)) {
            return false;
        }

        if ($this->http_data['type'] !== 'response') {
            throw new Exception('bad request');
        }

        return true;
    }

    public function get_header() {
        $http_data = $this->http_data;

        $header = implode("\r\n", $http_data['header']);
        if ($http_data['code'] == 200) {
            $header .= "\r\nContent-Length: " . strlen($this->body);
        }

        return "{$http_data['line']}\r\n{$header}";
    }

    public function get_response() {
        $header = $this->get_header();

        $result = "{$header}\r\n\r\n";

        if ($this->http_data['code'] == 200 && $this->body !== '') {
            $result .= $this->body;
        }

        return $result;
    }

    public function get_info() {
        $size = 0;

        if ($this->body !== '') {
            $size = strlen($this->body);
        }

        $size = \App\Command\BaseCommand::show_size($size);

        return "{$this->http_data['code']} {$size}";
    }

    protected function decode_header($header_string) {
        $line = parent::decode_header($header_string);

        $info = explode(' ', $line);
        if (count($info) < 3) {
            throw new Exception('request error');
        }

        $http_data = $this->http_data;

        if (strncasecmp($info[0], 'http', 4) !== 0) {
            throw new Exception('非法数据');
        }

        $http_data['line'] = $line;
        $http_data['type'] = 'response';
        $http_data['code'] = intval($info[1]);
        if ($http_data['length'] === -1 && $http_data['code'] != 200) {//不是200则没有响应内容
            $http_data['length'] = 0;
        }
        $this->http_data = $http_data;
    }

}
