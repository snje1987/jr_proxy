<?php

namespace App\Controler;

use App\Model\PlayerInfo;
use App\Model\Calculator;
use Exception;

class Boat extends BaseControler {

    public function __construct($router) {
        parent::__construct($router);
    }

    public function c_index() {

        $play_info = new PlayerInfo();

        $material = $play_info->get_material_ships();
        $target = $play_info->get_target_ships();

        $values = \App\Config::get('main', 'values');
        $points = \App\Config::get('main', 'points');

        $this->display_tpl('ship/index', [
            'material' => $material,
            'target' => $target,
            'values' => $values,
            'points' => $points,
        ]);
    }

    public function c_calc() {
        try {
            $msg = [
                'error' => 1,
                'msg' => '操作失败',
            ];

            $target = isset($_POST['target']) ? strval($_POST['target']) : '';
            $material_cid = isset($_POST['material']) ? strval($_POST['material']) : '';

            $material_cid = json_decode($material_cid, true);

            if (empty($target) || empty($material_cid)) {
                throw new Exception('参数不合法');
            }

            $player_info = new PlayerInfo();

            $target_ship = $player_info->get_target_ship($target);
            if ($target_ship === null) {
                throw new Exception('强化目标不存在');
            }

            $material_ships = $player_info->get_material_ships($material_cid);

            if (empty($material_ships)) {
                throw new Exception('强化素在不存在');
            }

            $calculator = new Calculator();

            $result = $calculator->cal($target_ship, $material_ships);

            $msg = [
                'error' => 0,
                'result' => $result,
            ];
        }
        catch (Exception $ex) {
            $msg = [
                'error' => 1,
                'msg' => $ex->getMessage(),
            ];
        }

        $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        $this->router->send($msg);
    }

}
