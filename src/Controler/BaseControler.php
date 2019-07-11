<?php

namespace App\Controler;

abstract class BaseControler {

    public static $default_function = 'index';
    protected $router;

    public function __construct($router) {
        $this->router = $router;
    }

    public function dispatch($function_name) {
        if ($function_name == '') {
            $function_name = static::$default_function;
        }

        $function_name = 'c_' . $function_name;

        if (!method_exists($this, $function_name)) {
            return $this->router->show_404();
        }
        call_user_func([$this, $function_name]);
    }

    public function send($msg) {
        $this->router->send($msg);
    }

}
