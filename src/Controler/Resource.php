<?php

namespace App\Controler;

use Workerman\Protocols\Http;

class Resource extends BaseControler {

    public function __construct($router) {
        parent::__construct($router);
    }

    public function dispatch($nouse) {
        $uri = isset($_SERVER['REQUEST_URI']) ? strval($_SERVER['REQUEST_URI']) : '';
        $pos = strpos($uri, '/', 1);
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

        $path = APP_RES_DIR . '/' . $file;
        if (file_exists($path)) {
            return $this->readfile($path);
        }
        return $this->router->show_404();
    }

    public function readfile($full) {
        $mtime = \filemtime($full);

        $expire = gmdate('D, d M Y H:i:s', time() + self::$cache_time) . ' GMT';
        $this->header('Expires: ' . $expire);
        $this->header('Pragma: cache');
        $this->header('Cache-Control: max-age=' . self::$cache_time);
        $this->header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        $this->header('Etag: ' . $mtime);

        $mime_type = self::get_mime($full);
        if ($mime_type !== null) {
            Http::header('Content-Type: ' . $mime_type);
        }

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $mtime) {
            $this->header('HTTP/1.1 304 Not Modified');
        }
        else {
            readfile($full);
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
