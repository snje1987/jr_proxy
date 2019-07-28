<?php

namespace App\Model;

use Exception;
use App\Http;

class WarReplayer {

    const REPLAY_FILE = APP_TMP_DIR . '/war_replay.json';

    protected $raw_data = null;
    protected $uid;

    public static function create_fack_user($data, $uid) {
        $data['uid'] = $uid;
        $data['username'] = '战斗回放';

        if (file_exists(self::REPLAY_FILE) && is_file(self::REPLAY_FILE)) {
            $data['resultLevel'] = 0;
        }
        else {
            $data['resultLevel'] = 1;
        }

        foreach ($data['ships'] as $k => $ship) {
            $ship['uid'] = $uid;
            $data['ships'][$k] = $ship;
        }

        foreach ($data['shipInfos'] as $k => $ship) {
            $ship['uid'] = $uid;
            $data['shipInfos'][$k] = $ship;
        }

        return $data;
    }

    public function __construct($uid) {
        if (file_exists(self::REPLAY_FILE) && is_file(self::REPLAY_FILE)) {
            $str = file_get_contents(self::REPLAY_FILE);
            $json = json_decode($str, true);
            if ($json !== null) {
                $this->raw_data = $json;
            }
        }

        $this->uid = $uid;
    }

    public function do_replay($step, $extra = true) {

        $response = new Http\Response();
        try {
            if ($this->raw_data == null) {
                throw new Exception();
            }

            switch ($step) {
                case 'spy':
                    $data = $this->replay_spy();
                    break;
                case 'challenge':
                    $data = $this->replay_challenge();
                    break;
                case 'result':
                    $data = $this->replay_result($extra);
                    break;
                default :
                    throw new Exception();
            }

            $str = json_encode($data);
            $body = zlib_encode($str, ZLIB_ENCODING_GZIP);

            $http_data = [
                'line' => 'HTTP/1.1 200 OK',
                'code' => 200,
                'header' => [
                    'Content-Type: application/octet-stream'
                ],
            ];

            $response = new Http\Response();
            $response->set_http_data($http_data);
            $response->set_body($body);
        }
        catch (Exception $ex) {
            $http_data = [
                'line' => 'HTTP/1.1 403 Forbidden',
                'code' => 403,
                'header' => [
                    'Content-Type: text/html'
                ],
            ];
            $response->set_http_data($http_data);
            $response->set_body('Access Not Allowed');
        }

        return $response;
    }

    //////////////////////////////

    protected function replay_spy() {
        if (!isset($this->raw_data['war_spy'])) {
            throw new Exception();
        }
        $data = $this->raw_data['war_spy'];

        if (!isset($data['enemyVO'])) {
            throw new Exception();
        }

        $ret = [
            'enemyVO' => $data['enemyVO'],
        ];

        $ret['enemyVO']['canSkip'] = 0;

        return $ret;
    }

    protected function replay_challenge() {
        if (!isset($this->raw_data['war_day'])) {
            throw new Exception();
        }
        $data = $this->raw_data['war_day'];

        if (!isset($data['warReport'])) {
            throw new Exception();
        }

        $ret = [];
        $ret['warReport'] = $data['warReport'];
        $ret['fuid'] = $this->uid;
        $ret['fUserVo'] = [
            'uid' => $this->uid,
            'username' => '战斗回放',
            'level' => '12'
        ];

        return $ret;
    }

    protected function replay_result($extra) {
        if (!isset($this->raw_data['war_result'])) {
            throw new Exception();
        }
        $data = $this->raw_data['war_result'];
        if (!isset($data['warResult'])) {
            throw new Exception('');
        }

        $ret = [];
        if ($extra && isset($data['extraProgress'])) {
            $ret['extraProgress'] = $data['extraProgress'];
        }
        $ret['warResult'] = $data['warResult'];
        $ret['shipVO'] = [];
        $ret['detailInfo'] = [];

        return $ret;
    }

}
