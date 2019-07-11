<?php

namespace App\Http;

use Workerman\Connection\TcpConnection;

class Request extends Base {

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

    public function get_api() {
        $host = $this->http_data['host'];

        if ($host === 'version.jr.moefantasy.com' || $host === 'version.channel.jr.moefantasy.com') {
            return ['fhx'];
        }

        if (preg_match('/s[0-9]+\.jr\.moefantasy\.com/', $host)) {
            $url = $this->http_data['url'];

            if (preg_match('/^\/?(\w+)\/(\w+).*$/', $url, $matches)) {
                $space_name = $matches[1];
                $class_name = $matches[2];

                return [$space_name, $class_name];
            }
        }
        return null;
    }

    public function get_info() {
        $request = $this->http_data['method'] . ' http://' . $this->http_data['host'] . ':' . $this->http_data['port'] . $this->http_data['url'];
        return $request;
    }

}
