<?php

/**
 * Listens for requests and forks on each connection
 */
$__server_listening = true;

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
//declare(ticks = 1);
//become_daemon();

/* nobody/nogroup, change to your host's uid/gid of the non-priv user */
//change_identity(65534, 65534);

/* handle signals */
//pcntl_signal(SIGTERM, 'sig_handler');
//pcntl_signal(SIGINT, 'sig_handler');
//pcntl_signal(SIGCHLD, 'sig_handler');

/* change this to your own host / port */
server_loop("0.0.0.0", 8765);

/**
 * Change the identity to a non-priv user
 */
function change_identity($uid, $gid) {
    if (!posix_setgid($gid)) {
        print "Unable to setgid to " . $gid . "!\n";
        exit;
    }

    if (!posix_setuid($uid)) {
        print "Unable to setuid to " . $uid . "!\n";
        exit;
    }
}

/**
 * Creates a server socket and listens for incoming client connections
 * @param string $address The address to listen on
 * @param int $port The port to listen on
 */
function server_loop($address, $port) {
    GLOBAL $__server_listening;

    if (($sock = socket_create(AF_INET, SOCK_STREAM, 0)) < 0) {
        echo "failed to create socket: " . socket_strerror($sock) . "\n";
        exit();
    }

    if (($ret = @socket_bind($sock, $address, $port)) === false) {
        echo "failed to bind socket\n";
        exit();
    }

    if (( $ret = socket_listen($sock, 0) ) < 0) {
        echo "failed to listen to socket: " . socket_strerror($ret) . "\n";
        exit();
    }

    socket_set_nonblock($sock);

    echo "waiting for clients to connect\n";

    while ($__server_listening) {
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

/**
 * Signal handler
 */
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

/**
 * Handle a new client connection
 */
function handle_client($ssock, $csock) {
    GLOBAL $__server_listening;

    $pid = pcntl_fork();

    if ($pid == -1) {
        /* fork failed */
        echo "fork failure!\n";
        die;
    } elseif ($pid == 0) {
        /* child process */
        $__server_listening = false;
        socket_close($ssock);
        interact($csock);
        socket_close($csock);
    } else {
        socket_close($csock);
    }
}

function interact($client) {
    $socket = false;
    while (($request = read_http($client)) !== '') {
        $info = analysis_request($request);
        if (!is_array($info)) {
            break;
        }
        $url = "[{$info['method']}] {$info['protical']}://{$info['host']}:{$info['port']}{$info['url']}";
        if ($socket === false) {
            $socket = connect_host($info);
            if ($socket === false) {
                echo "[ERROR] 连接服务器失败：{$url}\n";
                break;
            }
        }
        $data = forward_request($socket, $info);
        if ($data === false) {
            break;
        }
        $len = strlen($data['data']);
        $prefix = '/index/checkVer/';
        $host = 'version.jr.moefantasy.com';
        $find = '"cheatsCheck":0';
        $replace = '"cheatsCheck":1';
        if ($info['host'] == $host && strncmp($info['url'], $prefix, strlen($prefix)) === 0) {
            if (strpos($data['data'], $find) !== -1) {
                $data['data'] = str_replace($find, $replace, $data['data']);
                echo "替换checkVer成功\n";
            }
        }

        $ret = socket_write($client, $data['data'], $len);
        if ($ret === false) {
            echo "[ERROR] 回送失败：{$url}\n";
            return false;
        }
        echo "[{$info['method']}] {$info['protical']}://{$info['host']}:{$info['port']}{$info['url']} [{$data['code']},{$len}]\n";
        if (!$info['keep-alive']) {
            socket_close($socket);
            $socket = false;
        }
    }
    if ($socket !== false) {
        socket_close($socket);
    }
}

function read_http($socket, $is_respond = false) {
    $buf = null;
    $data = '';
    $retry = 0;
    $length = -1;
    $received = 0;
    while (true) {
        $bytes = socket_recv($socket, $buf, 2048, MSG_DONTWAIT);
        if ($bytes === false) {
            $err = socket_last_error($socket);
            if ($err != SOCKET_EAGAIN) {
                echo "[ERROR] 读取出错：[{$err}]" . socket_strerror($err) . "\n";
                break;
            }
            socket_clear_error($socket);
            $bytes = 0;
        }
        if ($bytes <= 0) {//没有数据则暂停100毫秒
            if ($retry ++ >= 50) {
                return $data;
            }
            usleep(100000);
        } else {
            $data .= $buf;
            $pos = 0;
            if ($length === -1) {//还未计算请求体长度
                if (($pos = strpos($data, "\r\n\r\n")) !== -1) {//请求头接收完毕
                    if ($is_respond) {
                        $code = get_code($data);
                        if ($code !== 200) {
                            return $data;
                        }
                    }
                    $matches = array();
                    if (preg_match('/^Content-Length:\s*(\d+)\s*$/im', $data, $matches)) {
                        $length = intval($matches[1]);
                        $received = strlen($data) - $pos - 4;
                        if ($received >= $length) {
                            return $data;
                        }
                    }
                }
            } else {//已计算请求体长度
                $received += strlen($buf);
                if ($received >= $length) {//接收数据完成
                    return $data;
                }
            }
        }
    }
    return '';
}

function get_code($header) {
    $pos = strpos($header, "\r\n");
    if ($pos === -1) {
        return 0;
    }
    $line = substr($header, 0, $pos);
    $data = explode(' ', $line);
    if (count($data) < 3) {
        echo '[ERROR]' . $header . "\n";
        return 0;
    }
    return intval($data[1]);
}

function analysis_request($request) {
    $pos = strpos($request, "\r\n");
    if ($pos === -1) {
        return false;
    }
    $line = substr($request, 0, $pos);
    $data = explode(' ', $line);
    if (count($data) !== 3) {
        echo '[ERROR]' . $line . "\n";
        return false;
    }
    $info = array();
    if ($data[0] === 'GET' || $data[0] === 'POST') {
        $url = $data[1];
        $matches = array();
        if (!preg_match('/^(https?):\/\/([a-z0-9-_.]+)(:(\d+))?(\S*)$/', $url, $matches)) {
            echo '[ERROR]' . $line . "\n";
            return false;
        }
        $info['method'] = $data[0];
        $info['url'] = $matches[5];
        $info['host'] = $matches[2];
        $info['protical'] = $matches[1];
        if ($matches[4] !== '') {
            $info['port'] = $matches[4];
        } else {
            if ($info['protical'] === 'https') {
                $info['port'] = 443;
            } else {
                $info['port'] = 80;
            }
        }
        $info['body'] = substr($request, $pos + 2);
        $info['http'] = $data[2];
        if (stripos($info['body'], "Connection: Keep-alive\r\n") !== -1) {
            $info['keep-alive'] = true;
        } else {
            $info['keep-alive'] = false;
        }
        return $info;
    } else {
        return false;
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

function forward_request($socket, $info) {
    $line = "{$info['method']} {$info['url']} {$info['http']}\r\n";
    $ret = socket_write($socket, $line . $info['body'], strlen($line . $info['body']));
    if ($ret === false) {
        echo '[ERROR] 发送请求失败';
        return false;
    }
    $data = read_http($socket, true);
    $code = get_code($data);
    return array(
        'data' => $data,
        'code' => $code,
    );
}

/**
 * Become a daemon by forking and closing the parent
 */
function become_daemon() {
    $pid = pcntl_fork();

    if ($pid == -1) {
        /* fork failed */
        echo "fork failure!\n";
        exit();
    } elseif ($pid) {
        /* close the parent */
        exit();
    } else {
        /* child becomes our daemon */
        posix_setsid();
        chdir('/');
        umask(0);
        return posix_getpid();
    }
}
