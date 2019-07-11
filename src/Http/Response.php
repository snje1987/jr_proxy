<?php

namespace App\Http;

use App\Http;

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

}
