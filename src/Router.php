<?php

namespace App;

class Router {

    protected $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function route($uri) {
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }
        if (!preg_match('/^(\/([a-z0-9_]*))?(\/([a-z0-9_]*)).*$/', $uri, $matches)) {
            return $this->show_404();
        }

        $class_name = isset($matches[2]) ? strval($matches[2]) : '';
        $function_name = isset($matches[4]) ? strval($matches[4]) : '';

        if ($class_name == '') {
            $class_name = 'Base';
        }
        else {
            $class_name = str_replace('_', '', ucwords($class_name, '_'));
        }

        $class_name = __NAMESPACE__ . '\\Controler\\' . $class_name;

        if (!class_exists($class_name)) {
            return $this->show_404();
        }

        $controler = new $class_name($this);
        $controler->dispatch($function_name);
    }

    public function show_404() {
        \Workerman\Protocols\Http::header('Content-type:text/plain', true, 404);
        $this->connection->send("Page Not Found");
    }

    public function send($msg) {
        if (is_array($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        $this->connection->send($msg);
    }

    public function redirect($url) {
        \Workerman\Protocols\Http::header('Location:' . $url, true, 302);
        $this->connection->send("");
    }

}
