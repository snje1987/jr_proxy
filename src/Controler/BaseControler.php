<?php

namespace App\Controler;

use Exception;

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
            $this->show_404();
        }
        call_user_func([$this, $function_name]);
    }

    public function header($header, $replace = true, $code = 0) {
        return $this->router->header($header, $replace, $code);
    }

    public function show_404() {
        $this->header('Content-type:text/plain', true, 404);
        throw new Exception('Page Not Found');
    }

    public function log($str) {
        fwrite(STDERR, $str . "\n");
    }

    protected function display_tpl($tpl, $vars, $content_type = 'text/html; charset=utf-8') {
        $this->tpl_path = APP_TPL_DIR . '/' . $tpl . '.php';
        if (!file_exists($this->tpl_path)) {
            return $this->router->show_404();
        }

        if ($content_type !== null) {
            $this->header('Content-type: ' . $content_type);
        }

        extract($vars, EXTR_OVERWRITE);
        require $this->tpl_path;
    }

}
