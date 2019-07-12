<?php

namespace App\Controler;

use Workerman\Protocols\Http;

abstract class BaseControler {

    public static $default_function = 'index';

    /**
     *
     * @var \App\Router
     */
    protected $router;
    protected $tpl_path;

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

    protected function display_tpl($tpl, $vars) {
        $this->tpl_path = APP_ROOT . '/src/theme/' . $tpl . '.php';
        if (!file_exists($this->tpl_path)) {
            return $this->router->show_404();
        }

        extract($vars, EXTR_OVERWRITE);

        ob_start();

        require $this->tpl_path;

        $result = ob_get_clean();

        Http::header('Content-type:text/html; charset=utf-8');

        $this->send($result);
    }

}
