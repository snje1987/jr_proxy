<?php

$server_listening = true;
$multy_client = true;
$debug = false;
$verbose = false;
foreach ($argv as $v) {
    if ($v === '-d') {
        $debug = true;
    } elseif ($v === '-v') {
        $verbose = true;
    } elseif ($v === '-s') {
        $multy_client = false;
    }
}

if ($debug) {
    define('DEBUG', true);
} else {
    define('DEBUG', false);
}
if ($verbose) {
    define('VERBOSE', true);
} else {
    define('VERBOSE', false);
}

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);

pcntl_signal(SIGTERM, 'sig_handler');
pcntl_signal(SIGINT, 'sig_handler');
pcntl_signal(SIGCHLD, 'sig_handler');

function sig_handler($sig) {
    switch ($sig) {
        case SIGTERM:
        case SIGINT:
            exit();
            break;
        case SIGCHLD:
            pcntl_waitpid(-1, $status);
            break;
    }
}

function microtime_float() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float) $usec + (float) $sec);
}

$mark = 0;

function mark_time() {
    global $mark;
    $now = microtime_float();
    $offset = $now - $mark;
    $mark = $now;
    return round($offset, 4);
}

function log_slow($msg) {
    $offset = mark_time();
    if ($offset > 1) {
        $msg = str_replace('{time}', $offset, $msg);
        echo $msg;
    }
}

server_loop("0.0.0.0", 8765);

function server_loop($address, $port) {
    global $server_listening;

    if (($sock = socket_create(AF_INET, SOCK_STREAM, 0)) === false) {
        echo "failed to create socket\n";
        exit();
    }

    if (($ret = @socket_bind($sock, $address, $port)) === false) {
        echo "failed to bind socket" . socket_strerror(socket_last_error($sock)) . "\n";
        exit();
    }

    if (( $ret = socket_listen($sock, 0) ) === false) {
        echo "failed to listen to socket: " . socket_strerror(socket_last_error($sock)) . "\n";
        exit();
    }

    socket_set_nonblock($sock);

    echo "waiting for clients to connect\n";

    while ($server_listening) {
        $connection = @socket_accept($sock);
        if ($connection === false) {
            usleep(100);
        } elseif ($connection > 0) {
            handle_client($sock, $connection);
        } else {
            echo "error: " . socket_strerror($connection);
            die;
        }
    }
}

function handle_client($ssock, $csock) {
    global $server_listening;
    global $multy_client;

    if ($multy_client) {
        $pid = pcntl_fork();

        if ($pid == -1) {
            /* fork failed */
            echo "fork failure!\n";
            die;
        } elseif ($pid == 0) {
            /* child process */
            $server_listening = false;
            socket_close($ssock);
            interact($csock);
            socket_close($csock);
        } else {
            socket_close($csock);
        }
    } else {
        interact($csock);
        socket_close($csock);
    }
}

function interact($client) {
    mark_time();
    $socket = false;
    while (($info = read_http($client)) !== '') {
        log_slow("读取请求: [{time}]\n");
        if (DEBUG) {
            print_r($info);
        }
        if (!is_array($info) || $info['type'] !== 'request') {
            break;
        }
        $url = "[{$info['method']}] {$info['protical']}://{$info['host']}:{$info['port']}{$info['url']}";

        $socket = connect_host($info);
        log_slow("连接目标: [{time}] {$url}\n");
        if ($socket === false) {
            echo "[ERROR] 连接服务器失败：{$url}\n";
            break;
        }
        $data = forward_request($socket, $info, $url);
        if ($data === false || !is_array($data) || $data['type'] !== 'respond') {
            break;
        }
        $len = strlen($data['body']);
        $prefix = '/index/checkVer/';
        $host = 'version.jr.moefantasy.com';
        $find = '"cheatsCheck":0';
        $replace = '"cheatsCheck":1';
        if ($info['host'] == $host && strncmp($info['url'], $prefix, strlen($prefix)) === 0) {
            if (strpos($data['body'], $find) !== false) {
                $data['body'] = str_replace($find, $replace, $data['body']);
                echo "替换checkVer成功\n";
            }
        }

        $respond = $data['line'] . "\r\n" . $data['header'] . "\r\n\r\n" . $data['body'];
        if (DEBUG) {
            echo $respond;
        }
        $len = strlen($respond);
        $ret = socket_write($client, $respond, $len);
        log_slow("返回结果: [{time}] {$url}\n");
        if ($ret === false) {
            echo "[ERROR] 回送失败：{$url}\n";
            return false;
        }
        echo "{$url} [{$data['code']},{$len}]\n";
        break;
    }
    if ($socket !== false) {
        socket_close($socket);
    }
}

function read_http($socket) {
    $buf = null;
    $data = '';
    $info = null;
    $retry = 0;
    $start = 0;
    while (true) {
        $bytes = socket_recv($socket, $buf, 2048, MSG_DONTWAIT);
        if ($bytes === false) {
            $err = socket_last_error($socket);
            if ($err != SOCKET_EAGAIN) {
                echo "[ERROR] 读取出错：[{$err}]" . socket_strerror($err) . "\n";
                return false;
            }
            socket_clear_error($socket);
            $bytes = 0;
        }
        if ($bytes <= 0) {//没有数据则暂停100毫秒
            if ($retry ++ >= 50) {
                echo "[ERROR] 读取超时\n";
                return false;
            }
            usleep(100000);
        } else {
            if ($info === null) {//还没有分析头部
                $data .= $buf;
                if (($pos = strpos($data, "\r\n\r\n")) !== false) {//请求头接收完毕
                    $info = analysis_header($data);
                    if ($info === false) {
                        return false;
                    }
                }
            } else {//已经分析头部
                $info['body'] .= $buf;
            }
            if ($info !== null) {
                if ($info['length'] >= 0) {
                    if (strlen($info['body']) >= $info['length']) {
                        return $info;
                    }
                } elseif ($info['length'] == -2) {//chunked
                    $start = analysis_chunk($info['body'], $start);
                    if ($start === -1) {
                        return $info;
                    }
                }
            }
        }
    }
}

function analysis_chunk($body, $start) {
    while (true) {
        if ($start >= strlen($body)) {
            return $start;
        }
        $pos = strpos($body, "\r\n", $start);
        if ($pos === false) {
            return $start;
        }
        $len = substr($body, $start, $pos - $start);
        $len = hexdec($len);

        if ($len === 0) {//最后一个chunk
            return -1;
        }
        $start = $len + 4 + $pos;
    }
}

function analysis_header($data) {
    $ret = array();
    $pos = strpos($data, "\r\n\r\n");
    if ($pos === false) {
        return false;
    }
    $header = substr($data, 0, $pos);
    $ret['body'] = substr($data, $pos + 4);

    $pos = strpos($header, "\r\n");
    if ($pos === false) {
        return false;
    }
    $line = substr($header, 0, $pos);
    $ret['header'] = substr($header, $pos + 2);

    $info = explode(' ', $line);
    if (count($info) < 3) {
        echo '[错误请求] ' . $line . "\n";
        return false;
    }

    if (preg_match('/^Content-Length:\s*(\d+)\s*$/im', $ret['header'], $matches)) {
        $ret['length'] = intval($matches[1]);
    } elseif (stripos($ret['header'], "Transfer-Encoding: chunked\r\n") !== false) {
        $ret['length'] = -2;
    } else {
        $ret['length'] = -1;
    }

    if (strncasecmp($info[0], 'http', 4) === 0) {//http响应
        $ret['line'] = $line;
        $ret['type'] = 'respond';
        $ret['code'] = intval($info[1]);
        if ($ret['length'] === -1 && $ret['code'] != 200) {//不是200则没有响应内容
            $ret['length'] = 0;
        }
        return $ret;
    } else {//http请求
        $ret['type'] = 'request';
        if ($info[0] === 'GET' || $info[0] === 'POST') {
            if ($info[0] === 'GET') {
                $ret['length'] = 0;
            }
            $url = $info[1];
            $matches = array();
            if (!preg_match('/^(https?):\/\/([a-z0-9-_.]+)(:(\d+))?(\S*)$/', $url, $matches)) {
                return false;
            }
            $ret['method'] = $info[0];
            $ret['url'] = $matches[5];
            $ret['host'] = $matches[2];
            $ret['protical'] = $matches[1];
            if ($matches[4] !== '') {
                $ret['port'] = $matches[4];
            } else {
                if ($ret['protical'] === 'https') {
                    $ret['port'] = 443;
                } else {
                    $ret['port'] = 80;
                }
            }

            $ret['http'] = $info[2];
            if (stripos($ret['header'], "Connection: Keep-alive\r\n") !== false) {
                str_replace("Connection: Keep-alive\r\n", "Connection: Close\r\n", $ret['header']);
            }
            return $ret;
        } else {
            echo '[暂不支持] ' . $line . "\n";
            return false;
        }
    }
}

$cache = array();

function connect_host($info) {
    global $cache;
    if (!isset($cache[$info['host']])) {
        $cache[$info['host']] = gethostbyname($info['host']);
    }
    $address = $cache[$info['host']];
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    if ($socket === false) {
        return false;
    }
    $result = @socket_connect($socket, $address, $info['port']);
    if ($result === false) {
        socket_close($socket);
        return false;
    }
    return $socket;
}

function forward_request($socket, $info, $url) {
    $request = "{$info['method']} {$info['url']} {$info['http']}\r\n{$info['header']}\r\n\r\n";
    if ($info['body'] !== '') {
        $request .= $info['body'];
    }
    if (DEBUG) {
        echo $request;
    }
    $ret = socket_write($socket, $request, strlen($request));
    log_slow("发送请求: [{time}] {$url}\n");
    if ($ret === false) {
        echo '[ERROR] 发送请求失败';
        return false;
    }
    $ret = read_http($socket);
    log_slow("读取结果: [{time}] {$url}\n");
    if (DEBUG) {
        print_r($ret);
    }
    return $ret;
}
