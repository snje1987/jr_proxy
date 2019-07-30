<?php

namespace App\Model;

class CurrentWar {

    const DEFAULT_WAR_LOG_PATH = '{uid}/{type}/{year}{month}{day}_{hour}{min}{sec}_{map}';
    const BASE_DIR = APP_DATA_DIR . '/warlog/';

    protected $file;
    protected $war_spy;
    protected $war_day;
    protected $war_result;
    protected $type;
    protected $name;
    protected $uid;

    public function __construct($uid) {
        $this->uid = $uid;

        $tmp_dir = APP_TMP_DIR . '/current_war/';
        if (!file_exists($tmp_dir)) {
            mkdir($tmp_dir, 0777, true);
        }

        $this->file = $tmp_dir . $this->uid . '.json';

        $this->load();
    }

    public function save() {
        $data = [
            'type' => $this->type,
            'name' => $this->name,
            'war_spy' => $this->war_spy,
            'war_day' => $this->war_day,
            'war_result' => $this->war_result,
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        file_put_contents($this->file, $json);

        return $this;
    }

    public function load() {
        if (file_exists($this->file)) {
            $json = file_get_contents($this->file);
            $data = json_decode($json, true);
        }
        else {
            $data = [];
        }

        $this->war_spy = isset($data['war_spy']) ? $data['war_spy'] : [];
        $this->war_day = isset($data['war_day']) ? $data['war_day'] : [];
        $this->war_result = isset($data['war_result']) ? $data['war_result'] : [];
        $this->type = isset($data['type']) ? strval($data['type']) : '';
        $this->name = isset($data['name']) ? strval($data['name']) : '';
        return $this;
    }

    public function set_spy($data) {
        $this->war_spy = $data;
        return $this;
    }

    public function set_day($data) {
        $this->war_day = $data;
        return $this;
    }

    public function set_result($data) {
        $this->war_result = $data;
        return $this;
    }

    public function set_type($type) {
        $this->type = $type;
        return $this;
    }

    public function set_name($name) {
        $this->name = $name;
        return $this;
    }

    public function save_log() {
        $file = $this->format_path();
        $dir = dirname($file);

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $data = [
            'type' => $this->type,
            'version' => LogUpgrader::VERSION,
            'war_spy' => $this->war_spy,
            'war_day' => $this->war_day,
            'war_result' => $this->war_result,
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        file_put_contents($file, $json);

        $this->war_spy = [];
        $this->war_day = [];
        $this->war_result = [];
        $this->type = '';
        $this->name = '';

        $this->save();

        return $this;
    }

    public function get_type() {
        return $this->type;
    }

    protected function format_path() {
        $vars = [];
        $vars['uid'] = $this->uid;
        $vars['type'] = $this->type;
        $vars['map'] = $this->name;

        $now = time();
        $vars['year'] = date('Y', $now);
        $vars['month'] = date('m', $now);
        $vars['day'] = date('d', $now);
        $vars['hour'] = date('H', $now);
        $vars['min'] = date('i', $now);
        $vars['sec'] = date('s', $now);

        $tpl = \App\Config::get('main', 'war_log_path', '');
        if ($tpl == '') {
            $tpl = self::DEFAULT_WAR_LOG_PATH;
        }
        $tpl = trim($tpl, '\\\/');

        foreach ($vars as $name => $val) {
            $tpl = str_replace('{' . $name . '}', $val, $tpl);
        }

        return self::BASE_DIR . $tpl . '.json';
    }

}
