<?php

namespace App\Model;

class LoginInfo {

    protected static $instance = null;
    protected static $timestamp = 0;

    const DATA_FILE = APP_DATA_DIR . '/login_info.json';

    protected $file;
    protected $key_hash = [];
    protected $uid_hash = [];

    /**
     * @return self
     */
    public static function get() {

        if (file_exists(self::DATA_FILE)) {
            $mtime = filemtime(self::DATA_FILE);
        }
        else {
            $mtime = 0;
        }

        if (self::$instance === null) {
            self::$instance = new self();
        }

        if (self::$timestamp != $mtime) {
            self::$instance->load();
            self::$timestamp = $mtime;
        }

        return self::$instance;
    }

    public function update($new_uid, $new_key) {

        if (isset($this->key_hash[$new_key])) {
            $old_uid = $this->key_hash[$new_key];
            if ($old_uid !== $new_uid) {
                unset($this->uid_hash[$old_uid]);
            }
        }
        elseif (isset($this->uid_hash[$new_uid])) {
            $old_key = $this->uid_hash[$new_uid];
            unset($this->key_hash[$old_key]);
        }

        $this->key_hash[$new_key] = $new_uid;
        $this->uid_hash[$new_uid] = $new_key;

        $this->save();

        return $this;
    }

    public function query_uid($ukey) {
        if (isset($this->key_hash[$ukey])) {
            return $this->key_hash[$ukey];
        }
        return null;
    }

    public function get_all_uids() {
        return array_keys($this->uid_hash);
    }

    /////////////////////////////////

    protected function __construct() {
        
    }

    protected function load() {
        if (file_exists(self::DATA_FILE)) {
            $json = file_get_contents(self::DATA_FILE);
            $data = json_decode($json, true);
        }
        else {
            $data = [];
        }

        $this->key_hash = isset($data['key_hash']) ? $data['key_hash'] : [];
        $this->uid_hash = isset($data['uid_hash']) ? $data['uid_hash'] : [];

        return $this;
    }

    protected function save() {
        $data = [
            'key_hash' => $this->key_hash,
            'uid_hash' => $this->uid_hash,
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        file_put_contents(self::DATA_FILE, $json);

        return $this;
    }

}
