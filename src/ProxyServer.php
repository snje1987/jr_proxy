<?php

namespace Site;

use Workerman\Worker;
use Exception;
use Workerman\Connection\AsyncTcpConnection;

class ProxyServer {

    public function __construct($ip = '0.0.0.0', $port = 14201) {
        $http_worker = new Worker("tcp://$ip:$port");
        $http_worker->count = 4;
        $http_worker->onMessage = [$this, 'on_request'];
        $http_worker->name = 'ProxyServer';
    }

    public function on_request($client, $buffer) {

        try {
            if (!isset($client->buffer) || $client->buffer === null) {
                $client->buffer = $buffer;
            }
            else {
                $client->buffer = $client->buffer . $buffer;
            }
            if (!$this->parse_header($client)) {
                return;
            }
            if (!$this->read_content($client)) {
                return;
            }

            $http_data = $client->http_data;

            $addr = "{$http_data['host']}:{$http_data['port']}";

            echo $addr . "\n";

            $remote = new AsyncTcpConnection("tcp://$addr");
            $remote->client = $client;

            $remote->onMessage = [$this, 'on_remote'];
            $remote->onConnect = [$this, 'on_remote_connect'];
            $remote->connect();
        }
        catch (Exception $ex) {
            $client->send($ex->getFile() . '[' . $ex->getLine() . ']' . $ex->getMessage());
            $client->close();
        }

        return;
    }

    public function on_remote($remote, $buffer) {
        if (!isset($remote->buffer) || $remote->buffer === null) {
            $remote->buffer = $buffer;
        }
        else {
            $remote->buffer = $remote->buffer . $buffer;
        }
        if (!$this->parse_header($remote)) {
            return;
        }
        if (!$this->read_content($remote)) {
            return;
        }
        $http_data = $remote->http_data;

        if (isset($http_data['gzip']) && $http_data['gzip'] == true) {
            $data = zlib_decode($remote->body);
        }
        else {
            $data = $remote->body;
        }
        if (strpos($data, '"cheatsCheck":0') !== false) {
            $data = str_replace('"cheatsCheck":0', '"cheatsCheck":1', $data);
            $data = str_replace('censor', '2', $data);
            echo "替换checkVer成功\n";
        }

        if (isset($http_data['gzip']) && $http_data['gzip'] == true) {
            $remote->body = zlib_encode($data, ZLIB_ENCODING_GZIP);
        }
        else {
            $remote->body = $data;
        }

        $header = implode("\r\n", $http_data['header']);
        $header .= "\r\nContent-Length: " . strlen($remote->body);

        $result = "{$http_data['line']}\r\n{$header}\r\n\r\n";

        if ($remote->body !== '') {
            $result .= $remote->body;
        }

        $client = $remote->client;
        $client->send($result);

        $client->close();
        $remote->close();
    }

    public function on_remote_connect($remote) {

        $client = $remote->client;
        $http_data = $client->http_data;
        $header = implode("\r\n", $http_data['header']);
        if ($http_data['method'] == 'POST') {
            $header .= "\r\nContent-Length: " . $http_data['length'];
        }

        $request = "{$http_data['method']} {$http_data['url']} {$http_data['http']}\r\n{$header}\r\n\r\n";
        if ($client->body !== '') {
            $request .= $client->body;
        }

        $remote->send($request);
    }

    protected function parse_header($connection) {
        $pos = strpos($connection->buffer, "\r\n\r\n");
        if ($pos === false) {
            return false;
        }
        $header_string = substr($connection->buffer, 0, $pos);
        $connection->buffer = substr($connection->buffer, $pos + 4);

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
                //$http_data['header'][] = $v;
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
            $http_data['type'] = 'respond';
            $http_data['code'] = intval($info[1]);
            if ($http_data['length'] === -1 && $http_data['code'] != 200) {//不是200则没有响应内容
                $http_data['length'] = 0;
            }
            $connection->http_data = $http_data;
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
                        $http_data['header'][$k] = 'Connection: Close';
                    }
                }

                $connection->http_data = $http_data;
                return true;
            }
            else {
                throw new Exception('request error');
            }
        }
    }

    protected function read_content($connection) {
        $data_len = $connection->http_data['length'];
        if ($data_len >= 0) {
            $len = strlen($connection->buffer);
            if ($len >= $connection->http_data['length']) {
                $connection->body = $connection->buffer;
                $connection->buffer = null;
                return true;
            }
        }
        elseif ($data_len === -2) {
            $pos = strpos($connection->buffer, "\r\n", 0);
            if ($pos === false) {
                return false;
            }
            while (true) {
                $len = substr($connection->buffer, 0, $pos);
                $len = hexdec($len);

                if ($len === 0) {//最后一个chunk
                    return true;
                }

                if ($len <= strlen($connection->buffer) - $pos - 4) {
                    $block = substr($connection->buffer, $pos + 2, $len);
                    if (!isset($this->body)) {
                        $connection->body = $block;
                    }
                    else {
                        $connection->body .= $block;
                    }

                    $connection->buffer = substr($connection->buffer, $pos + $len + 4);
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
