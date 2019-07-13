<?php

namespace App\Controler;

use Workerman\Protocols\Http;

class Resource extends BaseControler {

    public function __construct($router) {
        parent::__construct($router);
    }

    public function dispatch($nouse) {
        $uri = isset($_SERVER['REQUEST_URI']) ? strval($_SERVER['REQUEST_URI']) : '';
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $file = substr($uri, $pos + 1);
        }
        else {
            $file = '';
        }

        $file = str_replace('..', '', $file);

        if ($file === '') {
            return $this->router->show_404();
        }

        $path = APP_ROOT . '/src/resource/' . $file;
        if (file_exists($path)) {
            return $this->readfile($path);
        }
        return $this->router->show_404();
    }

    public function readfile($full) {
        $mtime = \filemtime($full);

        $expire = gmdate('D, d M Y H:i:s', time() + self::$cache_time) . ' GMT';
        Http::header('Expires: ' . $expire);
        Http::header('Pragma: cache');
        Http::header('Cache-Control: max-age=' . self::$cache_time);
        Http::header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        Http::header('Etag: ' . $mtime);

        $mime_type = self::get_mime($full);
        if ($mime_type !== null) {
            Http::header('Content-Type: ' . $mime_type);
        }

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $mtime) {
            Http::header('HTTP/1.1 304 Not Modified');
            $this->router->send('');
        }
        else {
            $content = file_get_contents($full);
            $this->router->send($content);
        }
    }

    public static function get_mime($full) {
        if (file_exists($full)) {
            $pinfo = pathinfo($full);
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            $mime_type = 'application/octet-stream';
            if (isset(self::$mime_hash[$ext])) {
                $mime_type = self::$mime_hash[$ext];
            }
            else {
                $fi = new \finfo(FILEINFO_MIME_TYPE);
                $mime_type = $fi->file($full);
            }
            return $mime_type;
        }
        return null;
    }

    public static $mime_hash = [
        'css' => 'text/css',
        'html' => 'text/html',
        'js' => 'text/javascript',
    ];
    public static $cache_time = 720000;

}