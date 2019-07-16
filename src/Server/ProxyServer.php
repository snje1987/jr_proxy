<?php

namespace App\Server;

use Workerman\Worker;
use Exception;
use Workerman\Connection\AsyncTcpConnection;
use App\Http;
use App\JrApi\BaseJrApi;

class ProxyServer {

    public function __construct() {
        $port = \App\Config::get('proxy_server', 'port', 14201);

        $http_worker = new Worker("tcp://0.0.0.0:$port");
        $http_worker->count = 4;
        $http_worker->onMessage = [$this, 'on_request'];
        $http_worker->name = 'ProxyServer';
    }

    public function on_request($client, $buffer) {

        try {
            if (!isset($client->request) || $client->request === null) {
                $client->request = new Http\Request();
            }

            if (!$client->request->init($buffer)) {
                return;
            }

            $api = $client->request->get_api();

            if ($api !== null) {
                $api_obj = BaseJrApi::create($api);
                $api_obj->before($client->request);
                $client->api_obj = $api_obj;
            }

            $info = $client->request->get_info();
            echo $info . "\n";

            $remote = new AsyncTcpConnection("tcp://" . $client->request->get_addr());
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
        if (!isset($remote->response)) {
            $remote->response = new Http\Response();
        }

        if (!$remote->response->init($buffer)) {
            return;
        }

        $client = $remote->client;
        $response = $remote->response;

        $info = $client->request->get_info();
        $info .= ' ' . $response->get_info() . "\n";

        echo $info;

        if (isset($client->api_obj)) {
            $client->api_obj->after($response);
        }

        $client->send($response->get_response());

        $client->close();
        $remote->close();
    }

    public function on_remote_connect($remote) {
        $request = $remote->client->request->get_request();

        $remote->send($request);
    }

}
